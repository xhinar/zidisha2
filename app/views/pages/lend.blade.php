@extends('layouts.master')

@section('page-title')
Lend
@stop

@section('content')
<div class="page-header">
    <h1>Lend</h1>
</div>


<div class="row">
    <div class="col-xs-4">
        <h2>Categories</h2>

        <ul class="list-unstyled">
            @foreach($loanCategories as $loanCategory)
            <li>
                @if($selectedLoanCategory == $loanCategory)
                    <strong>{{ $loanCategory->getName()}}</strong>
                @else
                    <a href="{{ route('lend:index') }}?loan_category_id={{ $loanCategory->getId() }}"> {{ $loanCategory->getName()}} </a>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    <div class="col-xs-8">
        @if($selectedLoanCategory)
        <h2>{{ $selectedLoanCategory->getName(); }}</h2>
        <br>

        <p><strong>How it works: </strong> {{ $selectedLoanCategory->getHowDescription() }} </p> <br>

        <p><strong>Why it's important: </strong> {{ $selectedLoanCategory->getWhyDescription() }} </p> <br>

        <p><strong>What your loan can do: </strong> {{ $selectedLoanCategory->getWhatDescription() }} </p>
        @endif
    </div>
</div>
@stop

