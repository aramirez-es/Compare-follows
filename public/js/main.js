/**
 * Javascript file to manage actions.
 *
 * @author Alberto Ramírez.
 */
$(document).ready(function()
{
    // Handle form sent to convert its in ajax request.
    Forms.handleSent("ajax_request", ManipulateResponse);
});

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateResponse =
{
    success: function(oReponse)
    {
        var oUser = oReponse;
//        var oUser = jQuery.parseJSON(oReponse);
        var oFigure = $("#user_1");
//        $(oFigure).after($(oFigure).clone());

        $(oFigure).find("figcaption")[0].innerText = oUser.name;
        $(oFigure).find("img")
            .attr("src", oUser.picture)
            .attr("alt", oUser.name);
        $(oFigure).find("em")[0]
            .innerText = "Followers: " + oUser.followers
            + " / Followings: " + oUser.followings;
    },
    error: function(){alert("Error");},
    timeout: function(){alert("Timeout");}
};