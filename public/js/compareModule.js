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