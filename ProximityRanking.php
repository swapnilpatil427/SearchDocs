<?php

namespace GrpRock\Project;

class ProximityRanking
{
    private $invertedIndexObj;
    public function __construct($invertedIndexObject)
    {
        $this->invertedIndexObj = $invertedIndexObject;
    }

    /* Calculates Proximity Rank of the query */
    public function CalculateProximityIndex($query, $tokenization_method)
    {
        $words = Tokenize(filter($query), $tokenization_method);
        $score = '';
        for ($docId = 1; $docId <= $this->invertedIndexObj->getDocCountFromADT(); ++$docId) {
            $rank = $this->getProximityScore($docId, $words);
            if ($rank != 0) {
                $score[$docId] = $rank;
            }
        }

        $this->printScore($score);
    }

    public function printScore($score)
    {
        if ($score != '') {
            arsort($score);
            echo "\nDocument ID\tRank\n";
            foreach ($score as $docId => $rank) {
                echo $docId."\t\t".$rank."\n";
            }
        } else {
            echo 'No Document Matching your Search';
        }
    }
    /* Gets the Proximity Score of the Document with Respect */
    public function getProximityScore($docId, $words)
    {
        $covers = '';
        $a = 0;
        $position = 0;
        while ($position < Infinity) {
            $a = $this->invertedIndexObj->nextCover($docId, $words, $position);
            if ($a[0] < Infinity) {
                $covers[$a[0]] = $a[1];
            }
            $position = $a[0];
        }
        $score = 0;
        if (empty($covers)) {
            return 0;
        } else {
            foreach ($covers as $key => $value) {
                $score += (1 / ($value - $key + 1));
            }

            return $score;
        }
    }
}
