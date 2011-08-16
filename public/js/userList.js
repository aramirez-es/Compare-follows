/**
 * Class to manage user list.
 *
 * @author Alberto Ramirez Fernandez
 */
UserList = function()
{
    this.nCurrentSelected = 0;
    this.aListUsers = [];
    this.aVisibleUsers = [];
    this.aActivatedUsers = [];
    this.aInputUsers = [];
    this.aHiddenValues = [];
}
/**
 * Set an array with list of posibles users.
 *
 * @param aList Object
 */
UserList.prototype.setList = function(aList)
{
    this.aListUsers = aList;
    return this;
}
/**
 * Set an array with list of hidden input of users ids.
 *
 * @param aList Object
 */
UserList.prototype.setHiddenContainers = function(aList)
{
    this.aHiddenValues = aList;
    return this;
}
/**
 * Initialize the current UserList.
 */
UserList.prototype.init = function()
{
    this.nCurrentSelected = 0;
    this.aVisibleUsers[0] = true;
}
/**
 * Add data from server to visible current user.
 *
 * @param oObjectToActivate Object
 * @param oUserData Object
 */
UserList.prototype.activate = function(oObjectToActivate, oUserData)
{
    this.nCurrentSelected = $(this.aListUsers).index(oObjectToActivate);

    this._replaceContentWithUserData(oObjectToActivate, oUserData);
    this._cleanUserIfExists(this.nCurrentSelected);

    this.aActivatedUsers[this.nCurrentSelected] = true;
    this.aInputUsers[this.nCurrentSelected] = oUserData.username;
    $(this.aHiddenValues[this.nCurrentSelected]).val(oUserData.username);

    this._showNext();

    return this.nCurrentSelected;
}
/**
 * Replace the content of user box with user data of response.
 *
 * @param oObjectToActivate Object
 * @param oUserData Object
 */
UserList.prototype._replaceContentWithUserData = function(oObjectToActivate, oUserData)
{
    $(oObjectToActivate).find("figcaption").text(oUserData.name);
    $(oObjectToActivate).find("img").attr("src", oUserData.picture).attr("alt", oUserData.name);
    $(oObjectToActivate).find("em").text("Followers: " + oUserData.followers + " / Followings: " + oUserData.followings);
    $(oObjectToActivate).find("input[type=search]").val(oUserData.username);
}
/**
 * Clear data of current user if exists.
 */
UserList.prototype._cleanUserIfExists = function(nIndex)
{
    this.aInputUsers[nIndex] = null;
    $(this.aHiddenValues[nIndex]).val("");
}
/**
 * Activate de next user to show if is possible.
 */
UserList.prototype._showNext = function()
{
    if (this.canAddMoreUsers())
    {
        var nSelected = this.nCurrentSelected + 1;

        $(this.aListUsers[nSelected]).fadeIn("fast");
        $(this.aListUsers[nSelected]).find("input[type=search]").focus();

        this.aVisibleUsers[this.nCurrentSelected] = true;
    }

}
/**
 * Check if the given username has been found yet.
 *
 * @param sUsername string
 */
UserList.prototype.checkIfExists = function(sUsername)
{
    var nIndex = 0;
    var nFound = this.aInputUsers.length;
    sUsername = this.formatUsername(sUsername);

    do
    {
        if (this.aInputUsers[nIndex] === sUsername)
        {
            return nIndex;
        }
        nIndex++;
    }
    while(nIndex < nFound);

    return false;
}
/**
 * Fomat the user name to unique possible way.
 *
 * @param sUsername string
 */
UserList.prototype.formatUsername = function(sUsername)
{
    if ("@" === sUsername.charAt(0))
    {
        sUsername = sUsername.substring(1);
    }

    return sUsername;
}
/**
 * Check if is possible add more content to compare.
 *
 * @return boolean
 */
UserList.prototype.canAddMoreUsers = function()
{
    var nNextUserToShow = this.nCurrentSelected + 1;

    return (true !== this.aVisibleUsers[nNextUserToShow] && nNextUserToShow < this.aListUsers.length);
}