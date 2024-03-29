import { cookieCheck, setUserName, getAvatar, getEmail, disconnect } from "./mainContent.js";

// Check if the cookie is set every second
setInterval(cookieCheck, 1000);

// -------------------------------------------------------
// --------------------- CARDS ---------------------------
// -------------------------------------------------------


// Get total time spent watching TV shows
function getTotalTime() {
    var email = window.user_email;

    if (email === null) {
        $("#time-passed").text("----");
    }

    $.ajax('../php/home.php/totaltime', {
        method: 'GET',
        data: {
            email: email
        },
    }).done(function (data) {

        // Convert the time to HH:MM:SS format
        var minutes = data;

        //if the user didn't watch anything
        if(minutes === null){
            $("#time-passed").text("00 minutes");
            return;
        }

        // If the user whatched less than 60 minutes
        // Convert the time to MM minutes format
        if(minutes < 60) {
            // Add a leading zero to the minutes if needed
            if (minutes < 10) {
                minutes = "0" + minutes;
            }

            $("#time-passed").text(minutes + " minutes");
        }
        else{
            var hours = Math.floor(minutes / 60);

            // If the user whatched less than 24 hours
            // Convert the time to HH:MM format
            if(hours < 24) {
                // Add a leading zero to the hours if needed
                if (hours < 10) {
                    hours = "0" + hours;
                }
                
                minutes = minutes % 60;
                // Add a leading zero to the minutes if needed
                if (minutes < 10) {
                    minutes = "0" + minutes;
                }

                $("#time-passed").text(hours + "H"+ minutes);
            }
            else{

                // If the user whatched more than 24 hours
                // Convert the time to DD:HH format
                var days = Math.floor(hours / 24);

                hours = hours % 24;

                // Add a leading zero to the hours if needed
                if (hours < 10) {
                    hours = "0" + hours;
                }

                $("#time-passed").text(days + "J " + hours + "H");
            }            
        }
    });
}

// Get the number of movies watched
function getMoviesWatched() {
    var email = window.user_email;

    if (email === null) {
        $("#movies-num").text("----");
    }

    $.ajax('../php/home.php/movieswatched', {
        method: 'GET',
        data: {
            email: email
        },
    }).done(function (data) {

        // If the user didn't watch any movie
        if(data === null){
            $("#movies-num").text("00");
            return;
        }

        var movies = data;

        // Add a leading zero to the movies if needed
        if (movies < 10) {
            movies = "0" + movies;
        }

        $("#movies-num").text(movies);
    });
}

// Get the number of episodes watched
function getEpisodesWatched() {
    var email = window.user_email;

    if (email === null) {
        $("#episodes-num").text("----");
    }

    $.ajax('../php/home.php/episodeswatched', {
        method: 'GET',
        data: {
            email: email
        },
    }).done(function (data) {

        // If the user didn't watch any episode
        if(data === null){
            $("#episodes-num").text("00");
            return;
        }

        var episodes = data;

        // Add a leading zero to the episodes if needed
        if (episodes < 10) {
            episodes = "0" + episodes;
        }

        $("#episodes-num").text(episodes);
    });
}

$(document).ready(function () {
    // Get the user's mail
    // When the promise is resolved, get the informations about the user
    getEmail().then(function() {
        // Get the user's name
        setUserName();

        getAvatar("#avatar-header");

        // Get the number of movies watched
        getMoviesWatched();

        // Get the number of episodes watched
        getEpisodesWatched();

        // Get total time spent watching TV shows
        getTotalTime();
    });
});

// -------------------------------------------------------
// ---------------------- LOGOUT -------------------------
// -------------------------------------------------------

// Disconnect the user when he clicks on the logout button
$("#logout").click(function () {
    alert("Vous êtes déconnecté"); // Alert the user that he is disconnected
    disconnect();
});

