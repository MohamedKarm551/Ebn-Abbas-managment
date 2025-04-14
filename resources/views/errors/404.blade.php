@extends('layouts.app') {{-- Assuming you have a main layout file --}}

@section('title', 'الصفحة غير موجودة') {{-- Optional: Set the page title --}}

@section('content')
<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Apply the gradient to the 404 text --}}
            <h1 class="display-1 fw-bold gradient-text mb-4">404</h1>
            <h2 class="mb-3">عذراً، الصفحة غير موجودة</h2>
            <p class="lead text-muted mb-4">
                الصفحة التي تبحث عنها ربما تم حذفها، أو تغير اسمها، أو أنها غير متاحة مؤقتاً.
            </p>
            {{-- Apply gradient style to the button --}}
            <a href="{{ url('/') }}" class="btn btn-gradient btn-lg">
                <i class="fas fa-home me-2"></i> العودة إلى الصفحة الرئيسية
            </a>
            {{-- You can add more links or information here if needed --}}
        </div>
    </div>
</div>

{{-- Optional: Add custom styles if needed --}}
<style>
    .gradient-text {
        background: linear-gradient(135deg, rgba(172, 44, 44, 0.9), rgba(211, 84, 84, 0.8)); /* Adjusted second color for better visibility */
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        /* Fallback for browsers that don't support background-clip: text */
        color: rgba(172, 44, 44, 0.9);
    }

    .btn-gradient {
        color: #fff; /* White text for better contrast */
        background-image: linear-gradient(135deg, rgba(172, 44, 44, 0.9) 0%, rgba(211, 84, 84, 0.8) 100%); /* Adjusted second color */
        background-color: rgba(172, 44, 44, 0.9); /* Fallback background color */
        border: none; /* Remove default border or set a matching one */
        transition: opacity 0.2s ease-in-out; /* Smooth transition on hover */
    }

    .btn-gradient:hover {
        color: #fff;
        opacity: 0.9; /* Slightly fade on hover */
        /* Alternatively, you could adjust the gradient or add a subtle shadow */
    }

    /* Ensure Font Awesome icons inherit the button color */
    .btn-gradient .fas {
        color: inherit;
    }
</style>
@endsection