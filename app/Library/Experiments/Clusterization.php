<?php

namespace App\Library\Experiments;

use App\Library\Map;

use App\Models\Geo\Place;

use App\Models\Ques\AnketaQuestion;
use App\Models\Ques\Qsection;
use App\Models\Ques\Question;

class Clusterization
{
    protected $clusters=[];
    protected $distances=[]; 
    protected $min_cl_distance = 0; // минимальное расстояние между кластерами на последнем шаге
    
    public static function init($places, $distances) {
        $clusters = [];
        foreach ($places as $place) {
            $clusters[] = [$place->id];
        }
        
        $clusterization = new Clusterization;
        $clusterization->setClusters($clusters, 1);  
        $clusterization->distances = $distances;
        
        return $clusterization;
    }
    
    public function setClusters($clusters, $step, $min=0) {
        $this->clusters[$step] = $clusters;
        $this->min_cl_distance = $min;
    }
    
    public function getClusters() {
        return $this->clusters;
    }
    
    public function getDistances() {
        return $this->distances;
    }
    
    public function getMinClusterDistance() {
        return $this->min_cl_distance;
    }
    
    /**
     * Get distances for all places
     * @param array $places
     * @param array $answers
     * @return array
     */
    public static function distanceForPlaces($places, $answers, $normalize=true, $weights=[]) {
        $distances = [];
        foreach ($places as $place1) {
            foreach ($places as $place2) {
               $distances[$place1->id][$place2->id] 
                       = Clusterization::distanceForAnswers($answers[$place1->id], $answers[$place2->id], $normalize, $weights);
            }
        }  
        return $distances;
    }
    
    public static function distanceForAnswers($answers1, $answers2, $normalize=true, $weights=[]) {
        $distance = 0;
        foreach ($answers1 as $qsection => $questions) {
            $difference = 0;
            foreach ($questions as $question => $answer) {
//                if (sizeof($answer) && sizeof($answers2[$qsection][$question]) 
  //                  && !sizeof(array_intersect(array_keys($answer), array_keys($answers2[$qsection][$question])))) {
                if (/*!sizeof($answer) || sizeof($answers2[$qsection][$question])
                    ||*/ !sizeof(array_intersect(array_keys($answer), array_keys($answers2[$qsection][$question])))) {
                    $difference += isset($weights[$qsection][$question]) ? $weights[$qsection][$question] : 1;
                }
            }
            $distance += $normalize ? $difference/sizeof($questions) : $difference;
        }
        
        return round($distance, 2);
    }
    
    public function completeLinkage($step, $distance_limit, $total_limit, $with_geo=false) {
        $clusters = $this->getClusters();
        
        $cluster_dist = $this->clusterDistances($clusters[$step]);
//dd($cluster_dist);        
        $min = min(array_values($cluster_dist));        
        // если минимальное расстояние между кластерами превысило предел и количество кластеров не больше лимита
        if ($min>$distance_limit && sizeof($clusters[$step]) <= $total_limit) {
            return; 
        }
        
        list($cluster_num1, $cluster_num2) = self::searchNearestClusters($clusters[$step], $cluster_dist, $min, $with_geo);
        $new_clusters = $this->mergeClusters($clusters[$step], $cluster_num1, $cluster_num2);
        $this->setClusters($new_clusters, $step+1, $min);
        if (sizeof($new_clusters)<2) {
            return;
        }        
        $this->completeLinkage($step+1, $distance_limit, $total_limit);
    }
    
    /**
     * 
     * @param array $clusters
     * @param array $min_cl_nums
     */
    public static function searchNearestClusters($clusters, $cluster_dist, $min, $with_geo=false) {
        if ($with_geo) {
            $cl_pair_nums = array_keys(array_filter($cluster_dist, function ($v) use ($min) {return $v==$min;}));
            return self::geoClusterDistances($clusters, $cl_pair_nums);
        }
        
        preg_match('/^(.+)\_(.+)$/', array_search($min, $cluster_dist), $nearest_cluster_nums);
        return [$nearest_cluster_nums[1], $nearest_cluster_nums[2]];        
    }

    public static function geoClusterDistances($clusters, $cl_pair_nums) {
        $min=1000;
        $num1=$num2=null;
        foreach ($cl_pair_nums as $pair) {
            preg_match('/^(.+)\_(.+)$/', $pair, $cl_nums);
            $cl_dist = self::geoClusterDistance($clusters[$cl_nums[1]], $clusters[$cl_nums[2]]);
            if ($cl_dist < $min) {
                $min=$cl_dist;
                $num1 = $cl_nums[1];
                $num2 = $cl_nums[2];
            }
        }
        return [$num1, $num2];
    }

    public static function geoClusterDistance($cluster1, $cluster2) {
        list($x1, $y1) = Place::geoCenter($cluster1);
        list($x2, $y2) = Place::geoCenter($cluster2);
        return sqrt(($x1-$x2)^2+($y1-$y2)^2);        
    }
    
    // вычисляем расстояния между всеми кластерами
    public function clusterDistances($clusters) {
        $cluster_dist = [];
//dd($this->getDistances(), $clusters);

        foreach ($clusters as $cluster1_num => $cluster1) {
            foreach ($clusters as $cluster2_num => $cluster2) {
                if ($cluster1_num != $cluster2_num) {
                   $cluster_dist[$cluster1_num.'_'.$cluster2_num] = $this->clusterDistance($cluster1, $cluster2);
                }
            }
        }
        return $cluster_dist;
    }
    
    // вычисляем расстояния между двумя кластерами
    public function clusterDistance($cluster1, $cluster2) {
        $distances = $this->getDistances();
        $max=0;
        foreach ($cluster1 as $p1) {
            foreach ($cluster2 as $p2) {
                if ($distances[$p1][$p2]>$max) {
                    $max = $distances[$p1][$p2];
                }
            }        
        }
        return $max;
    }
    
    public function mergeClusters($clusters, $merge_num, $unset_num) {
        $clusters[$merge_num] = array_merge($clusters[$merge_num], $clusters[$unset_num]);
        unset($clusters[$unset_num]);
        return $clusters;
    }
    
    public static function dataForMap($clusters, $places, $qsection_ids, $question_ids, $cl_colors) {
        $default_markers = Map::markers();
        $cluster_places = $markers =[];
        $count=0;
        $new_markers = sizeof($cl_colors) != sizeof($clusters) || sizeof(array_diff(array_keys($cl_colors), array_keys($clusters)));
        foreach ($clusters as $cl_num => $cluster) {
            $cur_color = $new_markers ? $default_markers[$count] : $cl_colors[$cl_num];
            $cluster_places[$cur_color] = [];
            foreach ($cluster as $place_id) {
                $place = $places->where('id', $place_id)->first();
                $anketa_count = $place->anketas()->count();
                $anketa_link = $anketa_count ? "<br><a href=/ques/anketas?search_place=".$place->id.">".$anketa_count." ".
                        trans_choice('анкета|анкеты|анкет', $anketa_count, [], 'ru')."</a><br>" : '';
                $answers = join(', ', $place->getAnswersForQsections($qsection_ids, $question_ids));
                $cluster_places[$cur_color][] 
                        = ['latitude'=>$place->latitude,
                           'longitude'=>$place->longitude,
                           'popup' => '<b>'.$place->name_ru.'</b>'.$anketa_link.$answers];
            }
            $markers[$cur_color] 
                    = //'<b>'. $cl_num. '</b>: '.
                    join(', ', AnketaQuestion::getAnswersForPlacesQsections($cluster, $qsection_ids, $question_ids));
            if ($new_markers) {
                $cl_colors[$cl_num] = $cur_color;    
            }
            $count++;
        }
        
        return [$markers, $cluster_places, $cl_colors];
    }
    
    public static function getRequestDataForView($request) {
        $total_answers = 1000;
        $place_ids = (array)$request->input('place_ids');
        $question_ids = (array)$request->input('question_ids');
        $normalize = (int)$request->input('normalize');
        $with_weight = (int)$request->input('with_weight');
        
        $qsection_ids = (array)$request->input('qsection_ids');
        if (!sizeof($qsection_ids)) {
            $qsection_ids = [2];
        }
        
        $places = Place::getForClusterization($place_ids, $total_answers);  
        
        return [$normalize, $place_ids, $places, $qsection_ids, $question_ids, $total_answers, $with_weight];
    }
    
    public static function getRequestDataForCluster($request, $places) {
//        $section_id = (int)$request->input('qsection_id');      
        $with_geo = (int)$request->input('with_geo');
        $cl_colors = (array)$request->input('cl_colors');        
        $distance_limit = $request->input('distance_limit');
        
        $total_limit = (int)$request->input('total_limit');
        if (sizeof($places)<$total_limit) {
            $total_limit = sizeof($places)-1;
        } elseif (!$total_limit || $total_limit<1 || $total_limit>20) {
            $total_limit = 20;
        }
        
        $method_values = [1=>'полной связи', //https://ru.wikipedia.org/wiki/%D0%9C%D0%B5%D1%82%D0%BE%D0%B4_%D0%BF%D0%BE%D0%BB%D0%BD%D0%BE%D0%B9_%D1%81%D0%B2%D1%8F%D0%B7%D0%B8
                          2=>'Соллина'
                         ];
        $method_id = isset($method_values[$request->input('method_id')]) 
                ? $request->input('method_id') : 1;
        
//        $section_values = [NULL=>'']+Qsection::getSectionListWithQuantity();
        $qsection_values = Qsection::getList();
        $question_values = Question::getList();
        $color_values = Map::markers(true);
        $place_values = $places->pluck('name_ru', 'id')->toArray();
        
        return [$color_values, $cl_colors, $distance_limit, $method_id, $method_values, $place_values, $qsection_values, $question_values, $total_limit, $with_geo];
    }
}