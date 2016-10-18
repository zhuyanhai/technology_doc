<?php    
//// A function that records the time when it is called
//function profile($dump = FALSE)
//{
//    static $profile;
//
//    // Return the times stored in profile, then erase it
//    if ($dump) {
//        $temp = $profile;
//        unset($profile);
//        return ($temp);
//    }
//
//    $profile[] = bcmul(microtime(true) , 1000, 0);
//}
//
//// Set up a tick handler
//register_tick_function("profile");
//
//// Initialize the function before the declare block
//profile();
//
//// Run a block of code, throw a tick every 2nd statement
//declare(ticks=2) {
//    for ($x = 1; $x < 50; ++$x) {
//        echo similar_text(md5($x), md5($x*$x)), "<br />;";
//    }
//}
//
//// Display the data stored in the profiler
//print_r(profile (TRUE));

print_r($_SERVER);