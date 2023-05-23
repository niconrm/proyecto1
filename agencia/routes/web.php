<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::metodo('peticion', acción );
Route::view('/hola', 'saludo');

Route::view('/inicio', 'inicio');
Route::get('/datos', function ()
{
    /* pasaje de datos a una vista */
    $marcas = [
            'Sansumg', 'iPhone', 'Xiaomi',
            'LG', 'Blackberry', 'Nokia'
    ];
    return view('datos',
                    [
                        'nombre'=>'marcos',
                        'numero'=>22,
                        'marcas'=> $marcas
                    ]
    );
});

/*##################
 * CRUD de regiones
 * */
Route::get('/regiones', function ()
{
    //obtenemos listado de regiones
    $regiones = DB::select('SELECT idRegion, regNombre
                               FROM regiones');
    return view('regiones', [ 'regiones'=>$regiones ]);
});
Route::get('/region/create', function ()
{
    return view('regionCreate');
});
Route::post('/region/store', function ()
{
    //capturamos dato enviado por el form
    //$regNombre = $_POST['regNombre'];
    //$regNombre = request()->input('regNombre');
    //$regNombre = request()->regNombre;
    $regNombre = request('regNombre');
    //insertarmos dato en tabla regiones
    try{
        DB::insert(
                'INSERT INTO regiones
                        ( regNombre )
                    VALUES
                        ( :regNombre )',
                    [ $regNombre ]
        );
        //redirección con mensaje ok
        return redirect('/regiones')
                ->with([
                        'mensaje'=>'Región: '.$regNombre.' agregada correctamente',
                        'css'=>'success'
                ]);
    }
    catch ( Throwable $th )
    {
        //redirección con mensaje error
        return redirect('/regiones')
                ->with([
                    'mensaje'=>'No se pudo agregar la región: '.$regNombre,
                    'css'=>'danger'
                ]);
    }
});
Route::get('/region/edit/{id}', function ( $id )
{
    //obtenemos datos de la región a modificar
    /* raw SQL
    $region = DB::select('SELECT idRegion, regNombre
                            FROM regiones
                            WHERE idRegion = :id',
                            [ $id ]
              );
    */
    /* Fluent Query Builder */
    $region = DB::table('regiones')
                    ->where('idRegion', $id)->first();
    //retornamos vista del form
    return view('regionEdit', [ 'region'=>$region ]);
});

Route::post('/region/update', function()
{
    $regNombre = request('regNombre');
    $idRegion = request('idRegion');
    try {
        /* raw SQL
        DB::UPDATE( 'UPDATE regiones
                        SET regNombre = :regNombre
                      WHERE idRegion = :idRegion',
                    [ $regNombre, $idRegion ]
        );*/
        DB::table('regiones')
                ->where('idRegion', $idRegion)
                ->update( ['regNombre'=>$regNombre] );

        //redirección con mensaje ok
        return redirect('/regiones')
            ->with([
                'mensaje'=>'Región: '.$regNombre.' modificada correctamente',
                'css'=>'success'
            ]);
    }
    catch ( Throwable $th )
    {
        //redirección con mensaje error
        return redirect('/regiones')
            ->with([
                'mensaje'=>'No se pudo modificar la región: '.$regNombre,
                'css'=>'danger'
            ]);
    }
});
Route::get('/region/delete/{id}', function ($idRegion)
{
    //obtenemos datos de una región por su id
    /*$region = DB::select(
                    'SELECT * FROM regiones WHERE idRegion = :idRegion',
                    [ $idRegion ]
    );*/
    $region = DB::table('regiones')
                    ->where('idRegion', $idRegion)->first();

    //chequeamos si hay destinos relacionado
    $cantidad = DB::table('destinos')
                    ->where('idRegion', $idRegion)->count();
    if( $cantidad > 0 ){
        return redirect('/regiones')
                    ->with(
                        [
                            'mensaje'=>'No se puede eliminar la región: '.$region->regNombre.' porque tiene destinso relacionados',
                            'css'=>'warning'
                        ]
                    );
    }
    return view('regionDelete', [ 'region'=>$region, 'cantidad'=>$cantidad ]);
});
Route::post('/region/destroy', function ()
{
    $regNombre = request('regNombre');
    $idRegion = request('idRegion');
    try {
        DB::table('regiones')
                ->where('idRegion', $idRegion)->delete();
        //redirección con mensaje ok
        return redirect('/regiones')
            ->with([
                'mensaje'=>'Región: '.$regNombre.' eliminada correctamente',
                'css'=>'success'
            ]);
    }
    catch ( Throwable $th )
    {
        //redirección con mensaje error
        return redirect('/regiones')
            ->with([
                'mensaje'=>'No se pudo eliminar la región: '.$regNombre,
                'css'=>'danger'
            ]);
    }
});

/*##################
 * CRUD de destinos
 * */
Route::get('/destinos', function ()
{
    //obtenemos listado de destinos
    /*$destinos = DB::select("SELECT *, regNombre FROM destinos as d
                              JOIN regiones as r
                                ON d.idRegion = r.idRegion");*/
    $destinos = DB::table('destinos as d')
                        ->join('regiones as r', 'd.idRegion', '=', 'r.idRegion')
                        ->get();
    return view('destinos', [ 'destinos'=>$destinos ]);
});

Route::get('/destino/create', function ()
{
    //obtenemos listado de regiones
    $regiones = DB::table('regiones')->get();
    return view('destinoCreate', [ 'regiones'=>$regiones ] );
});

Route::post('/destino/store', function ()
{
    //capturamos datos enviados por el form
    $destNombre = request('destNombre');
    $idRegion = request('idRegion');
    $destPrecio = request('destPrecio');
    $destAsientos = request('destAsientos');
    $destDisponibles = request('destDisponibles');
    try {
        DB::table('destinos')
                ->insert(
                    [
                        'destNombre'=>$destNombre,
                        'idRegion'=>$idRegion,
                        'destPrecio'=>$destPrecio,
                        'destAsientos'=>$destAsientos,
                        'destDisponibles'=>$destDisponibles
                    ]
                );
        return redirect('/destinos')
            ->with([
                'mensaje'=>'Destino: '.$destNombre.' agregado corectamente',
                'css'=>'success'
            ]);
    }
    catch ( Throwable $th ){
        return redirect('/destinos')
                ->with([
                    'mensaje'=>'No se pudo agregar el destino: '.$destNombre,
                    'css'=>'danger'
                ]);
    }
});
Route::get('/destino/edit/{id}', function ($id)
{
    //obtenemos listado de regiones
    $regiones = DB::table('regiones')->get();
    return view('destinoEdit', [ 'regiones'=>$regiones ]);
});
