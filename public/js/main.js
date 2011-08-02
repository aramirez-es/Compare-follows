/**
 * Javascript file to manage actions.
 *
 * @author Alberto Ramírez.
 */

/**
 * Function to show an error message.
 *
 * @param sMessage string
 */
showErrorMessage = function(sMessage)
{
    $("#message").text(sMessage)
        .slideDown("fast")
        .delay(2000)
        .fadeOut("slow");
}

/**
 * Configurate view on ajax start and stop
 */
configurateAjaxLoading = function()
{
    $("#loading").ajaxStart(function()
    {
        $(this).show();
    });
    $("#loading").ajaxStop(function()
    {
        $(this).hide();
    });
}

/**
 * Instance of class to manage list of users.
 *
 * @var UserList
 */
var oInstanceUserList = null;

/**
 * Document is loaded.
 */
$(document).ready(function()
{
    configurateAjaxLoading();

    oInstanceUserList = new UserList();
    oInstanceUserList
        .setList($(".users_figure"))
        .setHiddenContainers($("#form_compare input[type=hidden][name^=compare]"))
        .init();

    // Handle form sent to convert its in ajax request.
    Forms.handleSent("compare_ajax", ManipulateCompare);
    Forms.handleSent("ajax_request", ManipulateResponse);
});

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateCompare =
{
    success: function(oResponse, oForm)
    {
        var nTotal  = oResponse.length;
        var oImage  = null;
        var oLink   = null;
        var sName   = null;
        var sPicture = null;
        var sUser   = null;

        $("#compare_results").empty();
        $("#compare_results").append("<p>" + nTotal + " Amigos en común</p>");

        for (var nIndex = 0; nIndex < nTotal; nIndex++)
        {
            sName = oResponse[nIndex].name;
            sPicture = oResponse[nIndex].picture;
            sUser = oResponse[nIndex].username;

            oImage  = '<img src="' + sPicture + '" alt="' + sName + '" width="48" />';
            oLink   = '<a href="http://twitter.com/' + sUser
                        + '" title="' + sName + '" target="_blank">'
                        + oImage + sName + '</a>';

            $("#compare_results").append("<p>" + oLink + "</p>");
        }
    }
};

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateResponse =
{
    success: function(oReponse, oForm)
    {
        var oUser = oReponse;
        var oFigure = $(oForm).parent();
        var nCurrentActivaed = oInstanceUserList.activate(oFigure, oUser);
        $("#compare_results").empty();

        if (1 === nCurrentActivaed)
        {
            $("#form_compare").fadeIn("fast");
        }
    },
    error: function()
    {
        showErrorMessage("User not found.");
    },
    timeout: function()
    {
        showErrorMessage("Network do not respond.");
    },
    validate: function(oForm)
    {
        var bIsValid    = true;
        var sInputUser  = $(oForm).find("input[type=search]").val();

        if (false !== oInstanceUserList.checkIfExists(sInputUser))
        {
            showErrorMessage("User found yet!");
            bIsValid = false;
        }

        return bIsValid;
    }
};