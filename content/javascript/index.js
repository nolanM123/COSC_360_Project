import { profile, signin } from "../javascript/popups.js"

$(document).ready(function () {
    $("textarea").on("input", function(){
        const $this = $(this);
        $this.height("");

        let paddingTop = parseFloat($this.css("padding-top"))
        let paddingBottom = parseFloat($this.css("padding-bottom"))
        let height = Math.min(this.scrollHeight - paddingTop - paddingBottom, 400);
        $this.height(height);
    });

    $("main>header .user-info").on("click", function () {
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid === 0) {
            signin();
        } else {
            profile();
        }
    });

    $(".user-info img").on("error", function () {
        this.src = "../images/icons/profile-icon.svg";
    });

    const $usericon = $("main>header img");
    const userid = $.cookie("userid");
    if (userid) {
        $usericon.attr("src", `../images/user-icons/user-${$.cookie("userid")}-icon.png`)
    } else {
        $usericon.attr("src", "../images/icons/profile-icon.svg");
    }
});