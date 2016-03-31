<?php

namespace GrpRock\Project;

use seekquarry\yioop\library\PhraseParser;
use seekquarry\yioop\library\processors\HtmlProcessor;

require_once 'GallopingSearch.php';
require_once 'vendor/autoload.php';
require_once 'InvertedIndexPartsConst.php';

function filter($words)
{
    return preg_replace('/[^ A-Za-z0-9\-]/', '', strtolower($words));
}

/* Tokenize the content based on the tokenzation words */
function Tokenize($contents, $tokenization_method)
{
    if ($tokenization_method == 'none') {
        return preg_split('/[\s]+/', $contents, -1, PREG_SPLIT_NO_EMPTY);
    } elseif ($tokenization_method == 'stem') {
        return PhraseParser::stemTerms($contents, 'en-US');
    } else {
        return PhraseParser::getNGramsTerm(preg_split('/[\s]+/', $contents, -1, PREG_SPLIT_NO_EMPTY), 5);
    }
}

class InvertedIndex implements InvertedIndexPartsConst
{
    public static $invertedIndex;
    private $lastOccurance;
    private $docCount;
    private $docTermCount;
    private $avgDocLength;
    /* Stores the Word in Inverted Index */
    private function storeWordInInvertedIndex($word, $cnt)
    {
        if (isset(self::$invertedIndex[$word]['count'])) { // keeps count of total number of times that word appears in dir.
            self::$invertedIndex[$word]['count'] += 1;
        } else {
            self::$invertedIndex[$word]['count'] = 1;
        }

        if (isset(self::$invertedIndex[$word][$doc_count]['count'])) {
            self::$invertedIndex[$word][$doc_count]['count'] += 1; // keeps count of total number of times word appear in file.
        } else {
            self::$invertedIndex[$word][$doc_count]['count'] = 1;
        }

        self::$invertedIndex[$word][$doc_count][] = $cnt + 1;
    }

    /* Returns the first occurance of the word in the corpus */
    public function first($t)
    {
        return self::$invertedIndex[$t][1][0];
    }
    /* Returns the last occurance of the word in the corpus */
    public function last($t)
    {
        return self::$invertedIndex[$t][1][count(self::$invertedIndex[$t][1]) - 2];
    }
    /* Search the Next Term in the Document with respect to current location */
    public function next($t, $current, $docId)
    {
        $obj = new GallopingSearch();
        $result = $obj->gallopingSearchNext($t, $current, $docId);

        return $result;
    }

    public function nextDoc($t, $current) {
        $obj = new GallopingSearch();
        $result = $obj->gallopingSearchNextDoc($t,$current);

        return $result;
    }

    public function prevDoc($t, $current) {
        $obj = new GallopingSearch();
        $result = $obj->gallopingSearchPrevDoc($t,$current);

        return $result;
    }

    /* Search the Prev Term in the Document with respect to current location */
    public function prev($t, $current, $docId)
    {
        $obj = new GallopingSearch();
        $result = $obj->gallopingSearchPrev($t, $current, $docId);

        return $result;
    }

    public function is_null()
    {
        return (self::$invertedIndex == null) ? true : false;
    }

    public function getInvertedIndex()
    {
        return self::$invertedIndex;
    }

    public function getAverageDocLength() {
        return $this->avgDocLength;
    }

    public function getTermInDoc($DocId) {
        return $this->docTermCount[$DocId];
    }

    public function getDocCountFromADT()
    {
        return $this->docCount;
    }
    /* Calculates the next cover for the set words. */
    public function nextCover($docId, $words, $position)
    {
        $u = Infinity;
        $v = 0;
        $max = -1;
        $min = Infinity;
        foreach ($words as $word) {
            $max = $this->next($word, $position, $docId);
            if ($max > $v) {
                $v = $max;
            }
        }
        if ($v == Infinity) {
            return [Infinity, Infinity];
        }

        foreach ($words as $word) {
            $min = $this->prev($word, $v + 1, $docId);
            if ($min < $u) {
                $u = $min;
            }
        }

        return [$u, $v];
    }

    /* Creates the Inverted Index */
    public function createInvertedIndex($dir, $tokenization_method)
    {
        if ($dir == null || $dir == '') {
            exit;
        }
        $doc_count = 1;
        $cnt = 0; // keeps the count of words in the files.
        $docTermCount = 0;
        $files = glob($dir.'*.*');
        $TotaldocWordCount = 0;
        natsort($files);
        $htmlProc = new HtmlProcessor([],20000,HtmlProcessor::CENTROID_SUMMARIZER);
        $content = file_get_contents('test_files/Demo.html');
        echo $content;
        $array = $htmlProc->process($content,"http://www.google.com");
        print_r($array);
        ///print_r($array);
        try {
            foreach ($files as $file) {
                ++$this->docCount;
                $filecontents = filter(file_get_contents($file));
                if ($tokenization_method != 'chargram') {
                    $words = Tokenize($filecontents, $tokenization_method);
                } else {
                    $words = Tokenize($filecontents, 'none');
                }
                $docTermCount = 0;
                for ($cnt = 0; $cnt < count($words); ++$cnt) {
                    $word = '';
                    if ($tokenization_method == 'chargram') {
                        $word = Tokenize($words[$cnt], $tokenization_method);
                    } else {
                        $word[0] = $words[$cnt];
                    }
                    foreach ($word as $wrd) {
                        /* for every word according to file index it stores it value in invertedIndex array. */
                        if ($wrd != '') {
                            if (isset(self::$invertedIndex[$wrd]['count'])) { // keeps count of total number of times that word appears in dir.
                                self::$invertedIndex[$wrd]['count'] += 1;
                            } else {
                                self::$invertedIndex[$wrd]['count'] = 1;
                            }

                            if (isset(self::$invertedIndex[$wrd][$doc_count]['count'])) {
                                self::$invertedIndex[$wrd][$doc_count]['count'] += 1; // keeps count of total number of times word appear in file.
                            } else {
                                self::$invertedIndex[$wrd][$doc_count]['count'] = 1;
                            }

                            self::$invertedIndex[$wrd][$doc_count][] = $cnt + 1;
                            ++$docTermCount;
                        }
                    }
                }
                $this->docTermCount[$doc_count] = $docTermCount;
                $TotaldocWordCount += $docTermCount;
                ++$doc_count;
            }
            $this->avgDocLength = $TotaldocWordCount/Count($this->docTermCount);
            echo $this->avgDocLength;
            if (self::$invertedIndex != '') {
                ksort(self::$invertedIndex);
            }
        } catch (ErrorException $e) {
            echo "There is some error or check the directory name. \n exiting...";
            exit;
        }
    }

    // Prints the Inverted Index
    public function printInvertedIndex()
    {
        $tempStr;
        try {
            foreach (self::$invertedIndex as $word => $occurs) {
                $tempStr = '';
                echo $word.':'.(count($occurs) - 1).':';
                foreach ($occurs as $docID => $locations) {
                    if ($docID != 'count') {
                        $tempStr .= '('.$docID;
                        foreach ($locations as $key => $value) {
                            $tempStr .= ','.$value;
                        }

                        $tempStr .= '),';
                    } else {
                        echo $locations, ':';
                    }
                }

                echo rtrim($tempStr, ',')."\n";
            }
        } catch (ErrorException $e) {
            echo "There is some error or check the directory name. \n exiting...";
            exit;
        }
    }
}
