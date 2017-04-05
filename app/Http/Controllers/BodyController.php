<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\SaveBodyRequest;

use App\Models\Body;
use App\Models\Country;

use Excel;
use Input;

class BodyController extends Controller
{
    public function getBodies(Body $body, Request $req) {
        $max_permission = $req->get('max_permission');
    	$search = array(
            'name'          =>  Input::get('name'),
            'city'          =>  Input::get('city'),
            'country_id'    =>  Input::get('country_id'),
    		'sidx'      	=>  Input::get('sidx'),
    		'sord'			=>	Input::get('sord'),
    		'limit'     	=>  empty(Input::get('rows')) ? 10 : Input::get('rows'),
            'page'      	=>  empty(Input::get('page')) ? 1 : Input::get('page')
    	);

        $export = Input::get('export', false);
        if($export) {
            $search['noLimit'] = true;
        }

        $antennae = $ant->getFiltered($search);

        if($export) {
            Excel::create('antennae', function($excel) use ($antennae) {
                $excel->sheet('antennae', function($sheet) use ($antennae) {
                    $sheet->loadView('excel_templates.antennae')->with("antennae", $antennae);
                });
            })->export('xlsx');
            return;
        }

    	$antennaCount = $ant->getFiltered($search, true);
    	if($antennaCount == 0) {
            $numPages = 0;
        } else {
            if($antennaCount % $search['limit'] > 0) {
                $numPages = ($antennaCount - ($antennaCount % $search['limit'])) / $search['limit'] + 1;
            } else {
                $numPages = $antennaCount / $search['limit'];
            }
        }

        $toReturn = array(
            'rows'      =>  array(),
            'records'   =>  $antennaCount,
            'page'      =>  $search['page'],
            'total'     =>  $numPages
        );

        $isGrid = Input::get('is_grid', false); // Checking if the caller is jqGrid -> if yes, we add actions to the response..

        foreach($antennae as $antenna) {
            $actions = "";
            if($isGrid) {
                if($max_permission == 1) {
                    $actions .= "<button class='btn btn-default btn-xs clickMeAnt' title='Edit' ng-click='vm.editBody(".$antenna->id.")'><i class='fa fa-pencil'></i></button>";
                }
            } else {
                $actions = $antenna->id;
            }
        	$toReturn['rows'][] = array(
        		'id'	=>	$antenna->id,
        		'cell'	=> 	array(
        			$actions,
        			$antenna->name,
                    $antenna->email,
                    $antenna->address,
                    $antenna->phone,
        			$antenna->city,
        			$antenna->country->name
        		)
        	);
        }

        return response(json_encode($toReturn), 200);
    }

    public function saveBody(Body $ant, Country $country, SaveBodyRequest $req) {
        $id = Input::get('id');
        if(!empty($id)) {
            $ant = $ant->findOrFail($id);
        }

        $ant->name = Input::get('name');
        $ant->city = Input::get('city');
        $ant->email = Input::get('email');
        $ant->address = Input::get('address');
        $ant->phone = Input::get('phone');

        $country_id = Input::get('country_id');
        $countryCheck = $country->findOrFail($country_id);

        $ant->country_id = $country_id;
        $ant->save();

        $toReturn['success'] = 1;
        return response(json_encode($toReturn), 200);
    }

    public function getBody(Request $req, Body $body) {
        $body->syncRoles($req->get('user'));
        $toReturn['success'] = 1;
        $toReturn['antenna'] = $body;
        return response()->json($body);
        return response(json_encode($toReturn), 200);
    }
}
