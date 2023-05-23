@extends('layouts.plantilla')

    @section('contenido')
        <h1>Proceso de datos</h1>

        {{ $nombre }}

        @if( $numero < 10 )
            es menor
        @else
            no es menor
        @endif

        @for( $n=0; $n<10; $n++ )
            {{$n}}<br>
        @endfor

        <ul>
        @foreach( $marcas as $marca )
            <li>{{ $marca }}</li>
        @endforeach
        </ul>

    @endsection
