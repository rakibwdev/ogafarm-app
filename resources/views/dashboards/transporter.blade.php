@extends('layouts.app')
@section('title', 'Transporter Dashboard')
@section('content')
@include('dashboards.partials.summary', [
    'title' => 'Transporter Dashboard',
    'cards' => [['Verification', 'Coming in the next milestone'], ['Vehicles', 'Coming in the next milestone'], ['Delivery Offers', 'Coming in the next milestone'], ['Assigned Jobs', 'Coming in the next milestone'], ['Availability', 'Coming in the next milestone']],
])
@endsection
