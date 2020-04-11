@extends('layouts.app')

@push('scripts-after')
    <script src="{{ mix('js/tagteams/index.js') }}"></script>
@endpush

@section('content-head')
<!-- begin:: Content Head -->
<div class="kt-subheader kt-grid__item" id="kt_subheader">
    <div class="kt-subheader__main">
        <h3 class="kt-subheader__title">Tag Teams</h3>
        <span class="kt-subheader__separator kt-subheader__separator--v"></span>
        <x-search />
        @include('tagteams.partials.filters')
    </div>
    <div class="kt-subheader__toolbar">
        <a href="{{ route('tag-teams.create') }}"
            class="btn btn-label-brand btn-bold">
            Add Tag Team
        </a>
    </div>
</div>

<!-- end:: Content Head -->
@endsection

@section('content')
<x-portlet title="Employed Tag Teams">
    <table id="tagteams_table" data-table="tagteams.index" class="table table-hover"></table>
</x-portlet>
@endsection

