@extends('layouts.app')
@section('title', 'Farmer Dashboard')
@section('content')
@include('dashboards.partials.summary', [
    'title' => 'Farmer Dashboard',
    'cards' => [['My Farm', 'Coming in the next milestone'], ['Add Product', 'Coming in the next milestone'], ['Orders', 'Coming in the next milestone'], ['RFQs', 'Coming in the next milestone'], ['Share My Storefront', 'Coming in the next milestone']],
])
@endsection
