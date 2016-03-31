<?php

namespace GrpRock\Project;

class CosineRanking
{
    private $invertedIndexObj;
    private $words;
    public function __construct($invertedIndexObject)
    {
        $this->invertedIndexObj = $invertedIndexObject;
    }

    /* Calculates the query Vector */
    public function calculateQueryVector()
    {
        $queryVector = '';
        foreach ($this->words as $word) {
            if (isset($queryFreq[$word])) {
                $queryFreq[$word] += 1; // keeps count of total number of times word appear in file.
            } else {
                $queryFreq[$word] = 1;
            }
        }
        foreach (InvertedIndex::$invertedIndex as $word => $value) {
            if (isset($queryFreq[$word])) {
                $queryTf = log($queryFreq[$word], 2) + 1;
            } else {
                $queryTf = 0;
            }
            $wordIdf = $this->calculateIDF($word);
            $queryVector[$word] = $queryTf * $wordIdf;
        }

        return $queryVector;
    }

    /* Calculates Document Vector for the Document */
    public function calculateDocumentVector($docId)
    {
        $docVector = '';
        foreach (InvertedIndex::$invertedIndex as $key => $value) {
            if (isset(InvertedIndex::$invertedIndex[$key][$docId]['count'])) {
                $docTf = log(InvertedIndex::$invertedIndex[$key][$docId]['count'], 2) + 1;
            } else {
                $docTf = 0;
            }
            $docIdf = $this->calculateIDF($key);
            $docVector[$key] = $docTf * $docIdf;
        }

        return $docVector;
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

    /* Calculates the Cosine similarity of two Vectors */
    public function calculateCosineSimilarity($queryVector, $docVector)
    {
        $dotProduct = 0;
        $query = 0;
        $document = 0;
        foreach ($docVector as $key => $value) {
            $dotProduct += ($docVector[$key] * $queryVector[$key]);
            $query += $queryVector[$key] * $queryVector[$key];
            $document += $docVector[$key] * $docVector[$key];
        }
        $document = sqrt($document);
        $query = sqrt($query);
        if ($query != 0 && $document != 0) {
            return $dotProduct / (($query) * ($document));
        } else {
            return 0;
        }
    }

    public function printScore($score)
    {
        if (empty($score)) {
            echo 'No Document Matching your Search';
        } else {
            echo "\nDocument ID\tRank\n";
            arsort($score);
            foreach ($score as $docId => $rank) {
                echo $docId."\t\t".$rank."\n";
            }
        }
    }

    /* Calculates the TF IDF values of the Query with respect to document */
    public function calculateCosineTF_IDF($query, $tokenization_method)
    {
        $queryVector = '';
        $rank = '';
        $this->words = Tokenize(filter($query), $tokenization_method);
        $queryVector = $this->calculateQueryVector();
        $docId = $this->minDocId(0);
        while ($docId < Infinity) {
            $docVector = $this->calculateDocumentVector($docId);
            $score = $this->calculateCosineSimilarity($queryVector, $docVector);
            if ($score != 0) {
                $rank[$docId] = $this->calculateCosineSimilarity($queryVector, $docVector);
            }

            $docId = $this->minDocId($docId);
        }

        $this->printScore($rank);
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
