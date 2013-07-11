<?php   
require 'facebook.php';
require 'config.php';

// the timezone needs to be California to show the
// times correctly from Facebook servers
date_default_timezone_set('America/Los_Angeles');

$eventids = array(
    //561293110559824, //Young Dems Pub Social
    //133967716795177, //Save our shools
    //531271706919951, //Family Event
    188069558023180, //Friday Fest
    213793282104053, //Training
    176578685845140 //Banquet
);

$facebook = new Facebook(array(
    'appId'  => 'FB_APPID',
    'secret' => 'FB_SECRET',
    'cookie' => true, // enable optional cookie support
));

$fql    =   "SELECT eid, name, start_time, end_time, location, description, pic_cover, ticket_uri FROM event WHERE eid IN (" . implode(",", $eventids) . ") ORDER BY start_time asc";

$param = array(
    'method'    => 'fql.query',
    'query'     => $fql,
    'callback'  => ''
);

$eventResult = $facebook->api($param);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <title>Facebook Event Widget</title>
    <meta property="og:title" content="Facebook Event Widget"/>
    <meta property="og:type" content="website"/>
    <meta property="og:image" content="http://profile.ak.fbcdn.net/hprofile-ak-snc4/50332_2319773550_5085_n.jpg"/>
    <meta property="og:site_name" content="brevardcountyyoungdemocrats"/>
    <meta property="fb:app_id" content="<?php echo FB_APPID; ?>">
    <meta property="og:description" content="Provides embedable widget for Facebook events"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Provides embedable widget for Facebook events">
    <meta name="author" content="Gil Creque">
    
    <!-- Le styles -->
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css" rel="stylesheet">
    <!--[if IE 7]>
      <link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome-ie7.min.css" rel="stylesheet">
    <![endif]-->

        <style>
            .thumbnails {
                text-align: center;
            }
            .thumbnails li {
                width: 75px;
                display: inline-block;
                *display: inline; /* ie7 support */
                zoom: 1;
            }
            .thumbnail {
                border: none !important;
                -webkit-box-shadow: none !important;
                box-shadow: none !important;
            }

        </style>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/bootstrap/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/bootstrap/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/bootstrap/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="/bootstrap/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="/bootstrap/ico/favicon.png">

</head>
<body>
    
    <div id="header">
        <div id="container">
            <div id="logo"></div>
        </div>
    </div>
    
    <div class="container">
        <div class="hero-unit well row-fluid">
            <h1 class="span12">Facebook Event Widget</h1>
        </div>

            <?php
            //print_r($eventResult);
            // loop through the data retrieved
            foreach( $eventResult as $keys => $values )
            {
                echo '<article class="row-fluid well"><div class="event_links span8">';
                //// set the start and end dates and times
                $start_date = date( 'm/d/y', $values['start_time'] );
                //  
                $start_time = date( 'g:i a', $values['start_time'] );
                $end_time = date( 'g:i a', $values['end_time'] );
                // print out the results
                echo '<a href="http://www.facebook.com/event.php?eid=' .
                $values['eid'] .
                '" title="RSVP on Facebook" target="_blank"><img class="img-rounded" id="pic-cover_'.
                $values['eid'] .
                '" src="' .
                $values['pic_cover']['source'] .
                '" alt="' .
                $values['description'] .
                ', ' .
                $values['location'] .
                ', ' .
                $start_date .
                ' ' .
                $start_time .
                ' - ' .
                $end_time .
                '" /></a>';
                
                echo '<br /><p class="lead"">' .  $values['description'] . '</p><br /><p><a href="http://www.facebook.com/event.php?eid=' .
                $values['eid'] .
                '" title="RSVP on Facebook" target="_blank" class="btn btn-primary btn-large"><i class="icon-check icon-large"></i></i> RSVP on Facebook</a>';
                if (!is_null($values['ticket_uri']))
                {
                    echo ' <a href="' . $values['ticket_uri'] . '" title="Purchase Ticket" target="_blank" class="btn btn-primary btn-large"><i class="icon-ticket icon-large"></i></i> Purchase a ticket</a>';
                }
                echo '</p></div>';

            
                $fqlAttending  = 'SELECT uid, pic_square, first_name, last_name, profile_url FROM user WHERE uid IN (select uid FROM event_member WHERE eid =' . $values['eid'] . 'and rsvp_status="attending")';

                $param2 = array(
                    'method'    => 'fql.query',
                    'query'     => $fqlAttending,
                    'callback'  => ''
                );

                $attendingResult = $facebook->api($param2);

                if ($attendingResult)
                {
                    echo '<div class="event_attending span4"><h3>Attending:</h3>';
                    echo '<ul class="thumbnails">';

                    foreach( $attendingResult as $keys => $values )
                    {
                        // print out the results
                        echo '<li>
                        <div class="thumbnail">
                        <a href="' .
                        $values['profile_url'] .
                        '" title="' .
                        $values['first_name'] .' '. $values['last_name'] .
                        '" target="_blank"><img class="profile_pic img-circle" id="profile_pic_'.
                        $values['uid'] .
                        '" src="' .
                        $values['pic_square'] .
                        '" alt="' .
                        $values['first_name'] .' '. $values['last_name'] .
                        '" />' .
                        '<h6>' .
                        $values['first_name'] .' '. $values['last_name'] .
                        '</h6></a>
                        </div>
                        </li>';
                    }
                 
                    echo '</ul>';
                    echo '</div>';
                }
            echo '</article>';
            }
            ?>
        </div>
    </div> 

    <div id="footer">
    </div> 
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script>
    (function($) {
        $.fn.uniformHeight = function() {
            var maxHeight   = 0,
                max         = Math.max;

            return this.each(function() {
                maxHeight = max(maxHeight, $(this).height());
            }).height(maxHeight);
        }
    })(jQuery);
    $(".thumbnails").find(".thumbnail").uniformHeight();
    </script>
</body>
</html>
