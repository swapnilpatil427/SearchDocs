<?php
/**
* A class that extends SplHeap for showing rankings in the Belgian
* soccer tournament JupilerLeague
*/
namespace GrpRock\Project;
class MyMaxHeap extends \SplMaxHeap
{
    public function compare($array1, $array2)
    {
        $values1 = array_values($array1);
        $values2 = array_values($array2);
        return $values1[0] - $values2[0];
    }
}
?>
