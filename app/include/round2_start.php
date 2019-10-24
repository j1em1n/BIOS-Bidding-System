<?php

function deleteFailedBids(){
    $bidDAO = new BidDAO();
    $failedBids = $bidDAO->getBidsByStatus("Fail");
    foreach($failedBids as $bid) {
        $bidDAO->delete($bid);
    }
}

?>