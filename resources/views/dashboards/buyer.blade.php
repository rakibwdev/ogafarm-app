@extends('layouts.app')
@section('title', 'Buyer Dashboard')
@section('content')
@include('dashboards.partials.summary', [
    'title' => 'Buyer Dashboard',
    'cards' => [['Browse Marketplace', 'Coming in the next milestone'], ['Saved', 'Coming in the next milestone'], ['Orders', 'Coming in the next milestone'], ['Create RFQ', 'Coming in the next milestone'], ['Group Orders', 'Coming in the next milestone']],
])
@endsection
