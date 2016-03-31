<?php

namespace GrpRock\Project;

/*
We have not considered the special chatacters.
All the words has been converted to lowercase.
Docments count has been stored in separate variable.
Count for occurance of word in all document is maintained,
Count for each word in each document is also maintained.
*/

ini_set('error_reporting', E_ALL);
require_once 'vendor/autoload.php';
require_once 'CosineRanking.php';
require_once 'InvertedIndex.php';
require_once 'ProximityRanking.php';
require_once 'Bm25Ranking.php';
require_once 'InvertedIndexPartsConst.php';
$dir                 = ''; // Stores the Directory to Scrawl
$query               = ''; // Stores the Query Passed by User
$ranking_method      = ''; // Stores the Ranking method Passed by User
$tokenization_method = ''; // Stores the tokenization method Passed by User
define('Infinity', 999999);
if (isset($argv[1]) && isset($argv[2]) && isset($argv[3]) && isset($argv[4])) {
    $dir   = $argv[1];
    $query = $argv[2];
    if ($argv[3] == 'cosine' || $argv[3] == 'proximity' || $argv[3] == 'bm25' || $argv[3] == 'bm25f') {
        $ranking_method = $argv[3];
    } // Sets the ranking method
    else {
        echo "ranking method can be either 'cosine' or 'proximity' or 'bm25' or 'bm25f' .. exiting...";
        exit();
    }
    if ($argv[4] == 'none' || $argv[4] == 'stem' || $argv[4] == 'chargram') {
        $tokenization_method = $argv[4];
    } // Sets the tokenization method.
    else {
        echo 'No valid tokenization_method passed. none will be considered by default';
        $tokenization_method = 'none';
    }
} // Checks Whether all the Arguments are passed
else {
    echo 'Make sure you have passed parameters in sequence - some_dir query ranking_method tokenization_method..exiting....';
    exit();
}

$invertedIndexObj = new InvertedIndex();
$invertedIndexObj->createInvertedIndex($dir, $tokenization_method);

if ($ranking_method == 'cosine') {
    $cosineObj = new CosineRanking($invertedIndexObj);
    $cosineObj->calculateCosineTF_IDF($query, $tokenization_method);
} //if $ranking_method == 'cosine' create CosineRanking class Object calculate the rank.
elseif ($ranking_method == 'proximity') {
    $proximityObj = new ProximityRanking($invertedIndexObj);
    $proximityObj->CalculateProximityIndex($query, $tokenization_method);
} //if $ranking_method == 'cosine' create Proximity class Object calculate the rank.
elseif ($ranking_method == 'bm25') {
    $bm25Obj = new Bm25Ranking($invertedIndexObj);
    $bm25Obj->calculateOkapiBm25Score($query, $tokenization_method);
}
else {
    echo 'Check the Ranking Method....';
}
