@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $token }}">

    <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Reset Password</h2>

    <!-- Email Address -->
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input 
            id="email" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror" 
            type="email" 
            name="email" 
            value="{{ old('email', $email) }}" 
            required 
            autofocus 
            autocomplete="username"
            placeholder="Enter your email"
        />
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
        <input 
            id="password" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
            type="password"
            name="password"
            required 
            autocomplete="new-password"
            placeholder="Enter new password"
        />
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
        <input 
            id="password_confirmation" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            type="password"
            name="password_confirmation"
            required 
            autocomplete="new-password"
            placeholder="Confirm new password"
        />
    </div>

    <!-- Submit Button -->
    <div class="mb-4">
        <button 
            type="submit" 
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Reset Password
        </button>
    </div>
</form>
@endsection