/**
 * Javascript file to manage actions.
 *
 * @author Alberto Ramírez.
 */
$(document).ready(function()
{
    // Configurate loading ajax content.
    $("#loading").ajaxStart(function()
    {
        $(this).show();
    });
    $("#loading").ajaxStop(function()
    {
        $(this).hide();
    });

    // Handle form sent to convert its in ajax request.
    Forms.handleSent("ajax_request", ManipulateResponse);
});

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateResponse =
{
    success: function(oReponse, oForm)
    {
        var oUser = oReponse;
        var oFigure = $(oForm).parent();

        $(oFigure).find("figcaption")[0].innerText = oUser.name;
        $(oFigure).find("img")
            .attr("src", oUser.picture)
            .attr("alt", oUser.name);
        $(oFigure).find("em")[0]
            .innerText = "Followers: " + oUser.followers
            + " / Followings: " + oUser.followings;
    },
    error: function()
    {
        $("#message")
            .text("User not found.")
            .slideDown("fast")
            .delay(2000)
            .fadeOut("slow");
    },
    timeout: function(){alert("Timeout");}
};