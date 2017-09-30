document.addEventListener("securitypolicyviolation", function (e) {
    var body = document.getElementsByTagName("body");
    var className = "admin-bar";
    var isAdmin = false;
    if (body.classList)
        isAdmin = body.classList.contains(className);
    else
        isAdmin = new RegExp('(^| )' + className + '( |$)', 'gi').test(body.className);

    if (isAdmin) {
        alert("There is a mixed content violation for the following URI:\n" + e.blockedURI);
    }
});
