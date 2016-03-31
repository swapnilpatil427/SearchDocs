<?php

namespace GrpRock\Project;

class GallopingSearch
{
    private $lastOccurance; // Caches the Last occurance of term in the Posting List
    private $lastDocOccurance; // Caches the last occurance of Doc in Document List

    /* Search the Next Term in the Document with respect to current location */
    public function gallopingSearchNext($term, $current, $docId)
    {
        $postList = ''; // Stores the Posting List for the Document
        $jump = 1;
        $low = 0;
        if (!isset(InvertedIndex::$invertedIndex[$term][$docId])) {
            return Infinity;
        } // Checks if Term is present in the Posting List
        else {
            $postList = InvertedIndex::$invertedIndex[$term][$docId];
        }
        if ($postList[0] > $current) {
            return $postList[0];
        } // Checks if first occurance of term in posting list is greater than current
        if ($postList[count($postList) - 2] <= $current) {
            return Infinity;
        } // Checks if last occurance of term in posting list is less than current
        if ($this->lastOccurance[$term] > 0 && $this->lastOccurance[$term] < $current) {
            $low = $this->lastOccurance[$term];
        } // If last occurance of term to value in Cache
        else {
            $low = 0;
        }
        $high = $low + $jump;
        while ((count($postList) > $high) && $current > $postList[$high]) {
            $low = $high;
            $jump = $jump * 2;
            $high = $low + $jump;
            if (!isset($postList[$high])) {
                break;
            } // if term does not exists at high position
        } // Stop the galloping search if we go beyond current
        if (count($postList) < $high) {
            $high = count($postList) - 2;
        }
        $nextIndex = $this->binarySearchNext($postList, $low, $high, $current);
        $this->lastOccurance[$term] = $nextIndex;
        if (isset($postList[$nextIndex])) {
            return $postList[$nextIndex];
        }
        else {
            return Infinity;
        }
    }

    /* Binary Search for the Next position of the word with respect to current */
    public function binarySearchNext($pl, $low, $high, $current)
    {
        if ($current < $pl[$low]) {
            return $low;
        }
        $mid = floor(($high + $low) / 2);
        if ($pl[$mid] > $current) {
            if ($pl[$mid - 1] <= $current) {
                return $mid;
            }
            else {
                return $this->binarySearchNext($pl, $low, $mid - 1, $current);
            }
        }
        elseif ($pl[$mid] < $current) {
            return $this->binarySearchNext($pl, $mid + 1, $high, $current);
        }
        else {
            return $mid + 1;
        }
    }

    /* Search the Previous Term in the Document with respect to current location */
    public function gallopingSearchPrev($term, $current, $docId)
    {
        $postList = '';
        if (!isset(InvertedIndex::$invertedIndex[$term][$docId])) {
            return Infinity;
        }
        else {
            $postList = InvertedIndex::$invertedIndex[$term][$docId];
        }
        $jump = 1;
        $low = 0;
        if ($postList[count($postList) - 2] < $current) {
            return $postList[count($postList) - 2];
        }
        if ($postList[0] > $current) {
            return Infinity;
        }
        if ($this->lastOccurance[$term] > 0 && $this->lastOccurance[$term] < $current) {
            $low = $this->lastOccurance[$term];
        }
        else {
            $low = 0;
        }
        $high = $low + $jump;
        while ((count($postList) - 2) > $high && $current > $postList[$high]) {
            $low = $high;
            $jump = $jump * 2;
            $high = $low + $jump;
            if (!isset($postList[$high])) {
                break;
            }
        }
        if (!isset($postList[$high])) {
            $high = count(InvertedIndex::$invertedIndex[$term][$docId]) - 2;
        }
        $nextIndex = $this->binarySearchPrev($postList, $low, $high, $current);
        $this->lastOccurance[$term] = $nextIndex;
        if (isset($postList[$nextIndex])) {
            return $postList[$nextIndex];
        }
        else {
            return Infinity;
        }
    }

    /* Binary Search for the previous position of the word with respect to current */
    public function binarySearchPrev($pl, $low, $high, $current)
    {
        $mid = floor(($high + $low) / 2);
        if ($pl[$mid] > $current) {
            if ($pl[$mid - 1] <= $current) {
                return $mid - 1;
            }
            else {
                return $this->binarySearchPrev($pl, $low, $mid - 1, $current);
            }
        }
        elseif ($pl[$mid] < $current) {
            return $this->binarySearchPrev($pl, $mid + 1, $high, $current);
        }
        else {
            return $mid - 1;
        }
    }

    // Search the Next Document containing Term
    public function gallopingSearchNextDoc($term, $current)
    {
        $docs = '';
        $jump = 1;
        $low = 0;
        if (!isset(InvertedIndex::$invertedIndex[$term])) {
            return Infinity;
        }
        else {
            $docs = array_keys(InvertedIndex::$invertedIndex[$term]);
        }
        if ($docs[1] > $current) {
            return $docs[1];
        }
        if ($docs[count($docs) - 1] <= $current) {
            return Infinity;
        }
        if ($this->lastDocOccurance[$term] > 0 && $this->lastDocOccurance[$term] < $current) {
            $low = $this->lastDocOccurance[$term];
        }
        else {
            $low = 0;
        }
        $high = $low + $jump;
        while ((count($docs) > $high) && $current > $docs[$high]) {
            $low = $high;
            $jump = $jump * 2;
            $high = $low + $jump;
            if (!isset($docs[$high])) {
                break;
            }
        }
        if (count($docs) < $high) {
            $high = count($docs) - 2;
        }
        $nextIndex = $this->binarySearchNext($docs, $low, $high, $current);
        $this->lastDocOccurance[$term] = $nextIndex;
        if (isset($docs[$nextIndex])) {
            return $docs[$nextIndex];
        }
        else {
            return Infinity;
        }
    }

    // Search the Prev Document containing Term
    public function gallopingSearchPrevDoc($term, $current)
    {
        $docs = '';
        $jump = 1;
        $low = 1;
        if (!isset(InvertedIndex::$invertedIndex[$term])) {
            return Infinity;
        }
        else {
            $docs = array_keys(InvertedIndex::$invertedIndex[$term]);
        }
        if ($docs[count($docs) - 2] < $current) {
            return $docs[count($docs) - 2];
        }
        if ($docs[1] > $current) {
            return Infinity;
        }
        if ($this->lastDocOccurance[$term] > 0 && $this->lastDocOccurance[$term] < $current) {
            $low = $this->lastDocOccurance[$term];
        }
        else {
            $low = 1;
        }
        $high = $low + $jump;
        while ((count($docs) - 2) > $high && $current > $docs[$high]) {
            $low = $high;
            $jump = $jump * 2;
            $high = $low + $jump;
            if (!isset($docs[$high])) {
                break;
            }
        }
        if (!isset($docs[$high])) {
            $high = count($docs) - 2;
        }
        $nextIndex = $this->binarySearchPrev($docs, $low, $high, $current);
        $this->lastDocOccurance[$term] = $nextIndex;
        if (isset($docs[$nextIndex]) && $nextIndex != 0) {
            return $docs[$nextIndex];
        }
        else {
            return Infinity;
        }
    }
}
