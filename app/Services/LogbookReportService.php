<?php

namespace App\Services;

use App\Models\Logbook;
use App\Models\LogbookEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Step 1: Create a dedicated service class for report generation
 * This keeps the logic organized and reusable
 */
class LogbookReportService
{
    /**
     * Generate a progress report for a logbook
     *
     * @param Logbook $logbook
     * @return \Illuminate\Http\Response
     */
    public function generateProgressReport(Logbook $logbook)
    {
        try {
            // Step 2: Gather all necessary data with UTF-8 cleaning
            $data = $this->prepareReportData($logbook);

            // Step 3: Create PDF with proper UTF-8 configuration
            $pdf = Pdf::loadView('reports.logbook-progress', $data)
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false, // Security: disable PHP in templates
                    'isRemoteEnabled' => false, // Security: disable remote content
                    'enable_font_subsetting' => true,
                    'pdf_backend' => 'CPDF',
                    'dpi' => 96,
                ]);

            // Step 4: Set PDF properties
            $pdf->setPaper('A4', 'portrait');

            // Step 5: Return downloadable PDF
            $filename = $this->generateFileName($logbook);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'logbook_id' => $logbook->id,
                'error' => $e->getTraceAsString()
            ]);

            throw new \Exception('PDF Generation Error: ' . $e->getMessage());
        }
    }

    /**
     * Step 2 Implementation: Prepare all data needed for the report with UTF-8 cleaning
     */
    private function prepareReportData(Logbook $logbook): array
    {
        // Clean the logbook data
        $cleanedLogbook = $this->cleanLogbookData($logbook);

        // Basic logbook information
        $totalEntries = $logbook->entries()->count();
        $completionPercentage = $logbook->total_sessions > 0
            ? round(($totalEntries / $logbook->total_sessions) * 100)
            : 0;

        // Recent entries (last 5) with cleaned data
        $recentEntries = $logbook->entries()
            ->latest()
            ->limit(5)
            ->get(['session_title', 'entry_date', 'created_at'])
            ->map(function ($entry) {
                return [
                    'session_title' => $this->cleanString($entry->session_title),
                    'entry_date' => $entry->entry_date,
                    'created_at' => $entry->created_at,
                ];
            });

        // Weekly progress calculation
        $weeklyProgress = $this->calculateWeeklyProgress($logbook);

        return [
            'logbook' => $cleanedLogbook,
            'report_date' => now(),
            'total_entries' => $totalEntries,
            'completion_percentage' => $completionPercentage,
            'remaining_sessions' => max(0, $logbook->total_sessions - $totalEntries),
            'recent_entries' => $recentEntries,
            'weekly_progress' => $weeklyProgress,
            'duration_days' => $logbook->start_date && $logbook->end_date
                ? $logbook->start_date->diffInDays($logbook->end_date)
                : 0,
        ];
    }

    /**
     * Clean logbook data to ensure UTF-8 compatibility
     */
    private function cleanLogbookData(Logbook $logbook): object
    {
        // Create a copy of the logbook with cleaned string fields
        $cleanedData = $logbook->toArray();

        // Fields that need UTF-8 cleaning
        $stringFields = [
            'title',
            'description',
            'course_name',
            'course_code',
            'session_title'
        ];

        foreach ($stringFields as $field) {
            if (isset($cleanedData[$field])) {
                $cleanedData[$field] = $this->cleanString($cleanedData[$field]);
            }
        }

        // Also clean related model data
        if ($logbook->department) {
            $cleanedData['department'] = [
                'name' => $this->cleanString($logbook->department->name),
                'id' => $logbook->department->id
            ];
        }

        if ($logbook->level) {
            $cleanedData['level'] = [
                'name' => $this->cleanString($logbook->level->name),
                'id' => $logbook->level->id
            ];
        }

        if ($logbook->creator) {
            $cleanedData['creator'] = [
                'name' => $this->cleanString($logbook->creator->name),
                'id' => $logbook->creator->id
            ];
        }

        // Convert back to object for template compatibility
        return (object) $cleanedData;
    }

    /**
     * Clean a string for UTF-8 compatibility
     */
    private function cleanString(?string $string): string
    {
        if (empty($string)) {
            return '';
        }

        // Convert to UTF-8 if not already
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
        }

        // Remove control characters that can cause PDF issues
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);

        // Remove or replace problematic characters
        $string = str_replace(['"', '"', '"', '"'], ['"', '"', "'", "'"], $string);

        // Ensure string is valid UTF-8
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        return trim($string);
    }

    /**
     * Calculate weekly progress statistics
     */
    private function calculateWeeklyProgress(Logbook $logbook): array
    {
        if (!$logbook->start_date) {
            return ['average' => 0, 'this_week' => 0];
        }

        $weeksElapsed = max(1, $logbook->start_date->diffInWeeks(now()));
        $totalEntries = $logbook->entries()->count();
        $thisWeekEntries = $logbook->entries()
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        return [
            'average' => round($totalEntries / $weeksElapsed, 1),
            'this_week' => $thisWeekEntries,
        ];
    }

    /**
     * Generate a safe filename for the PDF
     */
    private function generateFileName(Logbook $logbook): string
    {
        $title = $this->cleanString($logbook->title ?? 'logbook');
        $safeTitle = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $title);
        $safeTitle = preg_replace('/-+/', '-', $safeTitle);
        $safeTitle = trim($safeTitle, '-');

        if (empty($safeTitle)) {
            $safeTitle = 'logbook';
        }

        return 'logbook-report-' . $safeTitle . '-' . $logbook->id . '-' . now()->format('Y-m-d') . '.pdf';
    }

    /**
     * Debug method to check for encoding issues
     */
    public function debugLogbookEncoding(Logbook $logbook): array
    {
        $issues = [];

        $fieldsToCheck = [
            'title' => $logbook->title,
            'description' => $logbook->description,
            'course_name' => $logbook->course_name,
            'course_code' => $logbook->course_code,
        ];

        foreach ($fieldsToCheck as $field => $value) {
            if ($value && !mb_check_encoding($value, 'UTF-8')) {
                $issues[] = [
                    'field' => $field,
                    'value' => $value,
                    'detected_encoding' => mb_detect_encoding($value),
                ];
            }
        }

        // Check recent entries
        $recentEntries = $logbook->entries()->latest()->limit(5)->get();
        foreach ($recentEntries as $entry) {
            if ($entry->session_title && !mb_check_encoding($entry->session_title, 'UTF-8')) {
                $issues[] = [
                    'field' => "entry_{$entry->id}_session_title",
                    'value' => $entry->session_title,
                    'detected_encoding' => mb_detect_encoding($entry->session_title),
                ];
            }
        }

        return $issues;
    }
}
