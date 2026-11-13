@extends('layouts.app')
@section('title', 'Job Seeker Dashboard')
@section('content')
@include('dashboards.partials.summary', [
    'title' => 'Job Seeker Dashboard',
    'cards' => [['Browse Jobs', 'Coming in the next milestone'], ['Applications', 'Coming in the next milestone'], ['Portfolio', 'Coming in the next milestone'], ['Messages', 'Coming in the next milestone'], ['Status', 'Coming in the next milestone']],
])
@endsection
