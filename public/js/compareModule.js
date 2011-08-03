/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateCompare =
{
    sName       : "",
    sPicture    : "",
    sUser       : "",
    success     : function(oResponse, oForm)
    {
        var nTotal      = oResponse.length;
        var oImage      = null;
        var oLink       = null;
        var oContainer  = null;

        $("#compare_results").empty();
        $("#compare_results").append("<p>" + nTotal + " Amigos en común</p>");

        for (var nIndex = 0; nIndex < nTotal; nIndex++)
        {
            this.sName      = oResponse[nIndex].name;
            this.sPicture   = oResponse[nIndex].picture;
            this.sUser      = oResponse[nIndex].username;

            oImage      = this.createImage();
            oLink       = this.createLink(oImage);
            oContainer  = this.createContainer(oLink);

            $("#compare_results").append(oContainer);
        }
    },
    createImage : function()
    {
        oImage = new Image();

        $(oImage).attr("src", this.sPicture);
        $(oImage).attr("alt", this.sName);
        $(oImage).attr("width", 48);

        return oImage;
    },
    createLink  : function(oImage)
    {
        oLink = document.createElement("a");

        $(oLink).attr("href", "http://twitter.com/" + this.sUser);
        $(oLink).attr("title", this.sName);
        $(oLink).attr("target", "_blank");
        $(oLink).append(oImage);
        $(oLink).append(this.sName);

        return oLink;
    },
    createContainer: function(oLink)
    {
        oContainer = document.createElement("div");

        $(oContainer).addClass("onefriend");
        $(oContainer).append(oLink);

        return oContainer;
    }
};