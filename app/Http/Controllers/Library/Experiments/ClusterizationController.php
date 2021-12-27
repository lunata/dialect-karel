<?php

namespace App\Http\Controllers\Library\Experiments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Library\Experiments\Clusterization;

use App\Models\Ques\Answer;

class ClusterizationController extends Controller
{
    public function __construct() {
        $this->middleware('auth:dict.edit,/experiments/');
    }
    
    public function index(Request $request) {
//print "<pre>";        
        list($normalize, $place_ids, $places, $qsection_ids, $question_ids, $total_answers, $with_weight)
                = Clusterization::getRequestDataForView($request);
        list($color_values, $cl_colors, $distance_limit, $method_id, $method_values, 
                $place_values, $qsection_values, $question_values, $total_limit, $with_geo) 
                = Clusterization::getRequestDataForCluster($request, $places);
//dd($place_values);        
        list($answers, $weights) 
                = Answer::getForPlacesQsection($places, $qsection_ids, $question_ids, $with_weight);        
        $distances = Clusterization::distanceForPlaces($places, $answers, $normalize, $weights);

        $clusterization = Clusterization::init($places, $distances, $method_id, $with_geo, $distance_limit, $total_limit);
        $clusterization->clusterization($method_id);
        $clusters = $clusterization->getClusters();
        $last_step = array_key_last($clusters);
//dd($clusters[$last_step]);        
        $min_cl_distance = $clusterization->getMinClusterDistance();
        
        list(/*$markers, */$cluster_places, $cl_colors) 
                = Clusterization::dataForMap($clusters[$last_step], $places, $qsection_ids, $question_ids, $cl_colors);
//dd($cl_colors);       
        
        return view('experiments/anketa_cluster/index', 
                compact('cl_colors', 'cluster_places', 'clusters', 'color_values', 
                        'distance_limit', 'last_step', 'method_id', //'section_id', 'markers', 
                        'method_values', 'min_cl_distance', 'normalize', 
                        'place_ids', 'place_values', 'qsection_ids', 
                        'qsection_values', 'question_ids', 'question_values', // 'section_values', 
                        'total_limit', 'with_geo', 'with_weight'));
    }
    
    public function viewData(Request $request) {
        list($normalize, $place_ids, $places, $qsection_ids, $question_ids, $total_answers, $with_weight)
                = Clusterization::getRequestDataForView($request);
        
        $place_names = $places->pluck('name_ru', 'id')->toArray();
        
        list($answers, $weights) 
                = Answer::getForPlacesQsection($places, $qsection_ids, $question_ids, $with_weight);        
        $distances = Clusterization::distanceForPlaces($places, $answers, $normalize, $weights);
//dd($answers, $differences);        
        return view('experiments/anketa_cluster/view_data', 
                compact('answers', 'distances', 'place_names'));
    }
}
