<?php
function getBiddingResults($section, $roundNum, $bidDAO, $sectionDAO) {

    $courseCode = $section->getCourse();
    $sectionNum = $section->getSection();
    $minBid = $section->getMinBid();

    // After every bid, the system sorts the 'pending' bids from the highest to the lowest
    $sectionBids = $bidDAO->getBidsBySectionStatus($courseCode, $sectionNum, 'Pending');

    // arrays to store (predicted) successful and unsucessful bids
    $successfulBids = [];
    $unsuccessfulBids = [];

    $vacancies = $section->getVacancies();
    // all bids can be accommodated if:
    // Round 1 - no. of pending bids < vacancies
    // Round 2 - no. of pending bids <= vacancies

    if (!empty($sectionBids)) {
        if (($roundNum == 1 && count($sectionBids) < $vacancies) || ($roundNum == 2 && count($sectionBids) <= $vacancies)) {
            $successfulBids = $sectionBids;

            // for round 2, if the number of bids equals the number of vacancies, min bid must be updated
            // 'price never goes down', so only update minbid if the lowest bid is higher than the current min bid
            if (count($sectionBids) == $vacancies && $minBid < $sectionBids[$vacancies-1]->getAmount()) {
                $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
                $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
            }
        } else {
            // amount bidded by the nth student, where n = no. of vacancies. This is the clearing price.
            $clearing = $sectionBids[$vacancies-1]->getAmount();
            
            // Round 1: get the (n-1)th bid (first successful bid above the nth bid)
            // if the nth and (n-1)th bids are tied, all bids at clearing price are unsuccessful
            if($roundNum == 1) {
                $above = $sectionBids[$vacancies-2]->getAmount();
            }
            
            // Round 2: get the (n+1)th bid (first unsuccessful bid below the nth bid)
            // if the nth and (n+1)th bids are tied, all bids at clearing price are unsuccessful
            if($roundNum == 2) {
                $below = $sectionBids[$vacancies]->getAmount();
            }
            
            if (($roundNum == 1 && $above == $clearing) || ($roundNum == 2 && $below == $clearing)) {
                foreach ($sectionBids as $bid) {
                    if ($bid->getAmount() > $clearing) {
                        $successfulBids[] = $bid;
                    } else {
                        $unsuccessfulBids[] = $bid;
                    }
                }
            } else {
                // otherwise, bids up to the nth bid can be accommodated and all bids below the clearing price are unsuccessful.
                $successfulBids = array_slice($sectionBids, 0, $vacancies);
                $unsuccessfulBids = array_merge($unsuccessfulBids, array_slice($sectionBids, $vacancies));
            }

            // update the minimum bid for round 2
            $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
            $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
        }
    }
    return [$successfulBids, $unsuccessfulBids];
}

function processBids() {
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $roundDAO = new RoundDAO();
    $round = $roundDAO->retrieveRoundInfo();
    $roundNum = $round->getRoundNum();

    $sections = $sectionDAO->retrieveAll();
    $studentDAO = new StudentDAO();
    foreach ($sections as $section) {
        $vacancies = $section->getVacancies();
        $results = getBiddingResults($section, $roundNum, $bidDAO, $sectionDAO);
        $coursecode = $section->getCourse();
        $sectionId = $section->getSection();
        $successfulBids = $results[0];
        $unsuccessfulBids = $results[1];

        foreach($successfulBids as $bid) {
            $bidDAO->updateBidStatus($bid, "Success");
            $vacancies--;
        }
        
        // update number of vacancies for this section
        $sectionDAO->updateVacancies($coursecode, $sectionId, $vacancies);
    
        foreach($unsuccessfulBids as $bid) {
            $bidDAO->updateBidStatus($bid, "Fail");
    
            // if bid is unsuccessful, refund student the full amount
            $refund = $bid->getAmount();
            $student = $studentDAO->retrieve($bid->getUserid());
            $newBalance = $student->getEdollar() + $refund;
            $studentDAO->updateEdollar($student->getUserid(), $newBalance);
        }
    }
}
?>