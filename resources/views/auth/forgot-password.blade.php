@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Forgot Password</h2>
    
    <p class="text-sm text-gray-600 mb-6 text-center">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
    </p>

    <!-- Email Address -->
    <div class="mb-6">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input 
            id="email" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror" 
            type="email" 
            name="email" 
            value="{{ old('email') }}" 
            required 
            autofocus
            placeholder="Enter your email"
        />
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div class="mb-4">
        <button 
            type="submit" 
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Email Password Reset Link
        </button>
    </div>

    <!-- Back to Login Link -->
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-900 hover:underline">
            Back to Login
        </a>
    </div>
</form>
@endsection