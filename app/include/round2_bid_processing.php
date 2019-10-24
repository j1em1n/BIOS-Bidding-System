<?php
require_once 'protect.php';
require_once 'common.php';

// Round 2 processing

// Initialise DAOs
$bidDAO = new BidDAO();
$sectionDAO = new SectionDAO();

// After every bid, the system sorts the 'pending' bids from the highest to the lowest
$sectionBids = $bidDAO->getBidsBySectionStatus($courseCode, $sectionNum, 'Pending');

// arrays to store (predicted) successful and unsucessful bids
$successfulBids = [];
$unsuccessfulBids = [];

$vacancies = $section->getVacancies();
// case 1: no. of live bids for the section <= vacancies (all bids can be accommodated)
if (count($sectionBids) <= $vacancies) {
    // since all bids can be accommodated, the predicted result will be 'Success' for all bids
    $successfulBids = $sectionBids;

    // if the number of bids equals the number of vacancies, min bid must be updated
    // 'price never goes down', so only update minbid if the lowest bid is higher than the current min bid
    if (count($sectionBids) == $vacancies && $minBid < $sectionBids[$vacancies]) {
        $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
        $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
    }
} else {
    // case 2: no. of live bids > vacancies 
    // get the lowest amount bidded. This acts as a temporary 'clearing price'
    $tempClearing = $sectionBids[$vacancies-1]->getAmount();

    // If the first unsuccessful bid below the lowest cleared bid is at the temp clearing price,
    // the implication is that there are tied bids that cannot be accommodated
    if ($sectionBids[$vacancies]->getAmount() == $tempClearing) {
        foreach ($sectionBids as $bid) {
            if ($bid->getAmount() > $tempClearing) {
                $successfulBids[] = $bid;
            } else {
                $unsuccessfulBids[] = $bid;
            }
        }
    } else {
        // otherwise, all tied bids can be accommodated, then all bids below the 'clearing price' are unsuccessful.
        $successfulBids = array_slice($sectionBids, 0, $vacancies);
        $unsuccessfulBids = array_merge($unsuccessfulBids, array_slice($sectionBids, $vacancies));
    }

    $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
    $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
}

// update the 'predicted' result of all the 'pending' bids in the database
foreach($successfulBids as $bid) {
    $bidDAO->updatePredicted($bid, "Success");
}
foreach($unsuccessfulBids as $bid) {
    $bidDAO->updatePredicted($bid, "Fail");
}
?>