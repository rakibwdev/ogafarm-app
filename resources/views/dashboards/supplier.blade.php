@extends('layouts.app')
@section('title', 'Supplier Dashboard')
@section('content')
@include('dashboards.partials.summary', [
    'title' => 'Supplier Dashboard',
    'cards' => [['Catalogue', 'Coming in the next milestone'], ['Add Input', 'Coming in the next milestone'], ['Quotes', 'Coming in the next milestone'], ['Orders', 'Coming in the next milestone'], ['Share My Storefront', 'Coming in the next milestone']],
])
@endsection
