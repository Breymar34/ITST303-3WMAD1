<?php
include './vendor/autoload.php';

// Use Sentiment\Analyzer;
// $analyzer = new Analyzer(); 
// $text = $analyzer->getSentiment("Your bitch!");
// print "Positive :".$text['pos']."  Negative :".$text['neg'];

use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Models\FeatureBasedNB;
use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\TokensDocument;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Classifiers\MultinomialNBClassifier;
 
// ---------- Data ----------------
// data is taken from http://archive.ics.uci.edu/ml/datasets/SMS+Spam+Collection
// we use a part for training
$training = array(
    array('ham','Go until jurong point, crazy.. Available only in bugis n great world la e buffet... Cine there got amore wat...'),
    ...
    array('ham','Fine if that\'s the way u feel. That\'s the way its gota b'),
    array('spam','England v Macedonia - dont miss the goals/team news. Txt ur national team to 87077 eg ENGLAND to 87077 Try:WALES, SCOTLAND 4txt/ú1.20 POBOXox36504W45WQ 16+')
);
// and another for evaluating
$testing = array(
    array('ham','I\'ve been searching for the right words to thank you for this breather. I promise i wont take your help for granted and will fulfil my promise. You have been wonderful and a blessing at all times.'),
    ...
    array('ham','I HAVE A DATE ON SUNDAY WITH WILL!!'),
    array('spam','XXXMobileMovieClub: To use your credit, click the WAP link in the next txt message or click here>> http://wap. xxxmobilemovieclub.com?n=QJKGIGHJJGCBL')
);
 
$tset = new TrainingSet(); // will hold the training documents
$tok = new WhitespaceTokenizer(); // will split into tokens
$ff = new DataAsFeatures(); // see features in documentation
 
// ---------- Training ----------------
foreach ($training as $d)
{
    $tset->addDocument(
        $d[0], // class
        new TokensDocument(
            $tok->tokenize($d[1]) // The actual document
        )
    );
}
 
$model = new FeatureBasedNB(); // train a Naive Bayes model
$model->train($ff,$tset);
 
 
// ---------- Classification ----------------
$cls = new MultinomialNBClassifier($ff,$model);
$correct = 0;
foreach ($testing as $d)
{
    // predict if it is spam or ham
    $prediction = $cls->classify(
        array('ham','spam'), // all possible classes
        new TokensDocument(
            $tok->tokenize($d[1]) // The document
        )
    );
    if ($prediction==$d[0])
        $correct ++;
}
 
printf("Accuracy: %.2f\n", 100*$correct / count($testing));