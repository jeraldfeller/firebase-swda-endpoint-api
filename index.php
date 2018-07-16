<?php
require 'Model/Init.php';
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);


require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

$sendMail = new SendMail();
$dateNow = date('Y-m-d');

$serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/safe-work-docs-app-85004c5b035e.json');

$firebase = (new Firebase\Factory())
    ->withServiceAccount($serviceAccount)
    ->create();

/**
 * Get List of users that will be search in database if the user is subscribed
 */
$authentication = $firebase->getAuth();
$users = $authentication->listUsers();
$userArray = array();
$usersEmail = array();
foreach ($users as $user) {
    $userArray[] = $user->uid;
    $usersEmail[$user->uid] = $user->email;
}

$database = $firebase->getDatabase();
$subscriptions = $database->getReference('subscriptions')->getSnapshot()->getValue();
foreach ($subscriptions as $uid => $value) {
    // if $uid exists in subscriptions and the status is not false it will be removed in the $userArray
    if (in_array($uid, $userArray)) {
        if($value != false){
            $index = array_keys($userArray, $uid)[0];
            unset($userArray[$index]);
        }
    }
}


// clean usersEmailArray



// now get the docs of the unsubscribed user filtered by DRAFT status

$subscriptions = $database->getReference('usersDocsOwnership')->getSnapshot()->getValue();
$docs = $database->getReference('docs')->getSnapshot()->getValue();
foreach($subscriptions as $uid => $userDocs){
    if (in_array($uid, $userArray)) {
        $draftDocs = array();
        foreach($userDocs as $obj => $uniqueId){
            foreach($docs as $docUniqueId => $data){
                if($uniqueId == $docUniqueId){
                    if($data['formStatus'] == 'DRAFT'){
                        $docTitle = $data['formData']['formTitle'];
                        $docId = $data['formUniqueID'];
                        $unix = $data['formCreated'] / 1000;
                        $formCreated = date('Y-m-d', $unix);
                        $formCreatedPlus30 = date('Y-m-d', strtotime($formCreated . ' +30 days'));
                        $formCreatedPlus31 = date('Y-m-d', strtotime($formCreated . ' +31 days'));
                        $formCreatedPlus35 = date('Y-m-d', strtotime($formCreated . ' +35 days'));
                        $formCreatedPlus40 = date('Y-m-d', strtotime($formCreated . ' +40 days'));
                        $now = new DateTime($dateNow);


                        $expireDate = new DateTime($formCreatedPlus30);
                        $diff = $expireDate->diff($now)->format("%a");

                        switch ($diff){
                            case 10:
                                echo 'Form created: ' . $formCreated . '<br>';
                                echo 'Form created + 30: ' . $formCreatedPlus30 . '<br>';
                                echo $diff . '<br>';
                                echo '--------------';
                                $numDays = 10;
                                $draftDocs[] = array(
                                    'id' => $docId,
                                    'title' => $docTitle,
                                    'numDays' => $numDays
                                );
                                break;
                            case 5:
                                echo 'Form created: ' . $formCreated . '<br>';
                                echo 'Form created + 30: ' . $formCreatedPlus30 . '<br>';
                                echo $diff . '<br>';
                                echo '--------------';
                                $numDays = 5;
                                $draftDocs[] = array(
                                    'id' => $docId,
                                    'title' => $docTitle,
                                    'numDays' => $numDays
                                );
                                break;
                            case 1:
                                echo 'Form created: ' . $formCreated . '<br>';
                                echo 'Form created + 30: ' . $formCreatedPlus30 . '<br>';
                                echo $diff . '<br>';
                                echo '--------------';
                                $numDays = 1;
                                $draftDocs[] = array(
                                    'id' => $docId,
                                    'title' => $docTitle,
                                    'numDays' => $numDays
                                );
                                break;
                            case 0:
                                echo 'Form created: ' . $formCreated . '<br>';
                                echo 'Form created + 30: ' . $formCreatedPlus30 . '<br>';
                                echo $diff . '<br>';
                                echo '--------------';
                                $numDays = 0;
                                $draftDocs[] = array(
                                    'id' => $docId,
                                    'title' => $docTitle,
                                    'numDays' => $numDays
                                );
                                break;
                        }

                    }
                }
            }
        }
        if(count($draftDocs) > 0){
            $message = '';
            for($i = 0; $i < count($draftDocs); $i++){
                $message .= 'Draft Doc '.$draftDocs[$i]['title'].'-'. $draftDocs[$i]['id'].' is about to expire in '.$draftDocs[$i]['numDays'].' days.'."\r\n";
            }
            $message .= 'To retain this document either finalise the doc or gain access to our subscription service.';

            $emailTo = $usersEmail[$uid];
            $sendData = array(
                'subject' => 'Draft doc expiry notification',
                'message' => $message,
                'emailTo' => 'jeraldfeller@gmail.com'
            );

            $sendMail->sendNotification($data);
            echo '<hr>';
        }

    }
}

