<?php
    require_once 'include/protect.php';
    require_once 'include/common.php';

// retrieve bids, process based on highest bids and vacancies available, place bids into table with ranking, bid price, state (successful/unsuccessful)
// update student e$ based on state

# check if round 1 is closed and round 2 is closed
$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
if ($roundInfo->getStatus() != "closed") {
    $_SESSION['errors'][] = "The round is not closed.";
    header("Location: admin_index.php");
    exit();
}
# retrieve bids for each section from database
    
$bidDAO = new BidDAO();

// retrieve all sections in database
$sectionDAO = new SectionDAO();
$sections = $sectionDAO->retrieveAll();
$studentDAO = new StudentDAO();

foreach ($sections as $section) {
    $coursecode = $section->getCourse();
    $sectionId = $section->getSection();
    $vacancies = $section->getVacancies();

    //get all bids for this particular section
    $sectionBids = $bidDAO->retrieveBidsBySection($coursecode, $sectionId);
    $successfulBids = [];
    $unsuccessfulBids = [];

    // all bids should be at least e$10.0 as per validations in other files, but we will double check here just in case
    for ($i=0; $i<count($sectionBids); $i++) {
        if ($sectionBids[$i]->getAmount() < 10.0) {
            // unset the bids that are below min bid and add them the the unsuccessful bids array
            $unsuccessfulBids[] = $sectionBids[$i];
            unset($sectionBids[$i]);
        }
    }

    if (count($sectionBids) >= $vacancies){
        // find clearing price only if only if there are at least n or more bids for a particular section, where n is the number of vacancies
        $lowestBid = $sectionBids[$vacancies-1];
        $clearingPrice = $lowestBid->getAmount();

        // count the number of bids that are at the clearing price
        $clearingCount = 0;
        foreach ($sectionBids as $bid) {
            if (floatval($bid->getAmount()) == floatval($clearingPrice)) {
                $clearingCount++;
            }
        }

        if ($clearingCount > 1) {
            // if there is more than one bid at clearing price, only bids higher than the clearing price are successful
            foreach ($sectionBids as $bid) {
                if ($bid->getAmount() > $clearingPrice) {
                    $successfulBids[] = $bid;
                } else {
                    $unsuccessfulBids[] = $bid;
                }
            }
        } else {
            // else if there is only one bid at clearing price, that bid will be the lowest successful bid
            $successfulBids = array_slice($sectionBids, 0, $vacancies);
            $unsuccessfulBids = array_merge($unsuccessfulBids, array_slice($sectionBids, $vacancies));
        }
    } else {
        // if there are less bids than vacancies, all bids are successful
        $successfulBids = $sectionBids;
    }

    // update the status of all the bids in the database
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





?>