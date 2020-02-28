<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r/{code}', function ($code) {
    $link = \App\Link::where('short_code', $code)->get()->first();
    $source_link = $link->source_link;
    //$source_link = \App\Link::where('short_code', $code)->value('source_link');
    $city = null;
    $country = null;

    $reader = new \GeoIp2\Database\Reader(resource_path() . '/GeoLite2/GeoLite2-City.mmdb');

    try {
        $record = $reader->city(request()->ip());
    } catch (\GeoIp2\Exception\AddressNotFoundException $exception) {
        //$record = $reader->city('88.214.10.164');
        $record = $reader->city(env('DEFAULT_IP_ADDR'));
    } finally {
        $city = $record->city->name;
        $country = $record->country->isoCode;
    }

    $result = file_get_contents('http://ip-api.com/json/' . request()->ip());
    $data = json_decode($result, true);

    if($data['status'] == 'fail') {
        $result = file_get_contents('http://ip-api.com/json/' . env('DEFAULT_IP_ADDR'));
        $data = json_decode($result, true);

        $city = $data['city'];
        $countryCode = $data['countryCode'];
    }

    $statistics = new \App\Statistic();
    $statistics->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
    $statistics->link_id = $link->id;
//    $statistics->ip = request()->server('REMOTE_ADDR');
//    $statistics->user_agent = request()->server('HTTP_USER_ADDR');
    $statistics->ip = request()->ip();
    $statistics->user_agent = request()->userAgent();
    $statistics->country_code = $countryCode;
    $statistics->city_name = $city;
    $statistics->save();

    dd($statistics);

    //    if($source_link == NULL) {
    //        return redirect('/');
    //    } else {
    //        return redirect($source_link);
    //    }
});
