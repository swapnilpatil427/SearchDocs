<?php

namespace GrpRock\Project;

require_once 'MyMaxHeap.php';
class Bm25Ranking
{
    const k1 = 1.2;
    const b = 0.75;
    private $invertedIndexObj;
    private $avgDocLen;
    private $words;
    private $result;

    public function __construct($invertedIndexObject)
    {
        $this->invertedIndexObj = $invertedIndexObject;
        $this->avgDocLen = $this->invertedIndexObj->getAverageDocLength();
        $this->result = new MyMaxHeap();
    }

    /* Calculates the TF IDF values of the Query with respect to document */
    public function calculateOkapiBm25Score($query, $tokenization_method)
    {
        $rank = '';
        $this->words = Tokenize(filter($query), $tokenization_method);
        print_r($this->words);
        $docId = $this->minDocId(0);
        //echo "doc" . $docId;
        while ($docId < Infinity) {
            $this->result->insert(array($docId => $this->calculateBm25Score($docId)));
            $rank[$docId] = $this->calculateBm25Score($docId);
            $docId = $this->minDocId($docId);
        }

        $this->printScore($rank);
    }

    public function printScore($score)
    {
        if ($this->result->isEmpty()) {
            echo 'No Document Matching your Search';
        } else {
            echo "\nDocument ID\tRank\n";
            foreach ($this->result as $number) {
                foreach ($number as $docId => $score) {
                    echo $docId."\t\t".$score."\n";
                }
            }
        }
    }

    public function calculateBm25Score($docId)
    {
        $score = 0;
        foreach ($this->words as $word) {
            $IDF = $this->calculateIDF($word);
            $TfBm25 = $this->calculateTfBm25($word, $docId);
            $score += ($IDF * $TfBm25);
            echo "\nDocID\t\tIDF\t\tTf\t\tScore\n";
            echo $docId."\t\t".$IDF."\t\t".$TfBm25."\t\t".$score;
        }

        return $score;
    }

    public function calculateTfBm25($word, $docId)
    {
        $freq = 0;
        if (isset(InvertedIndex::$invertedIndex[$word][$docId])) {
            $freq = InvertedIndex::$invertedIndex[$word][$docId]['count'];
        } else {
            return 0;
        }
        $docLength = $this->invertedIndexObj->getTermInDoc($docId);
        $numerator = $freq * (self::k1 + 1);
        $denominator = $freq * (self::k1 * ((1 - self::b) + (self::b * ($docLength / $this->avgDocLen))));

        return $numerator / $denominator;
    }

    /* Calculate Inverted document Frequency IDF of the word  */
    public function calculateIDF($word)
    {
        $docWithTerms = 0;
        for ($i = 1; $i <= $this->invertedIndexObj->getDocCountFromADT(); ++$i) {
            if (isset(InvertedIndex::$invertedIndex[$word][$i])) {
                ++$docWithTerms;
            }
        }

        return log(($this->invertedIndexObj->getDocCountFromADT() / $docWithTerms), 2);
    }

    public function minDocId($current)
    {
        $min = Infinity;
        foreach ($this->words as $word) {
            $pos = $this->invertedIndexObj->nextDoc($word, $current);
            if ($pos < $min) {
                $min = $pos;
            }
        }

        return $min;
    }
}
