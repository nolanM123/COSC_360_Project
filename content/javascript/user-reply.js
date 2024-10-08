import { validBody } from "../javascript/modules/authenticate.js";
import { profile, signin } from "../javascript/popups.js";

$(document).ready(function () {
    $(document).on("click", ".user-reply .user-info button", function () {
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid === 0) {
            signin();
        } else {
            profile();
        }
    });

    $(document).on("submit", ".user-reply .container-content", function (event) {
        event.preventDefault();

        const $this = $(this);
        const $parentid = $this.find("[name='parentid']");
        const $body = $this.find("[name='body']");

        $body.removeClass("highlighted");

        let valid = true;
        if (!validBody($body.val())) {
            $body.addClass("highlighted");
            valid = false;
        }
        if (valid) {
            $.post("../php/comment-actions/create.php", { parentid: $parentid.val(), body: $body.val() })
                .done(function () {
                    $body.val("");
                })
                .fail(function (xhr) {
                    if (xhr.status === 401) {
                        signin();
                    }
                });
        }
    });
});