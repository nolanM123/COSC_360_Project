import { validHead, validBody } from "../javascript/modules/authenticate.js"
import { profile, signin } from "../javascript/popups.js";
import { getPost } from "../javascript/content.js";

const $userEntryTemplate = $(await $.get("./templates/user-entry.html"));
const $username = $userEntryTemplate.contents().find(".user-info .username");
const $usericon = $userEntryTemplate.contents().find(".user-info button img");
const userid = $.cookie("userid");
const username = $.cookie("username");
if (userid) {
    $username.text(`@${username}`);
    $usericon.attr("src", `../images/user-icons/user-${$.cookie("userid")}-icon.png`)
} else {
    $username.text("@you");
    $usericon.attr("src", "../images/icons/profile-icon.svg");
}

$(document).ready(function () {
    const $main = $("main");
    $main.find(">header").after($userEntryTemplate.contents().clone());
    const $aside = $("aside");
    $aside.append($userEntryTemplate.contents().clone());

    $("#chat-btn").on("click", function () {
        let index = -1;

        const $containers = $main.find(">.container");
        $containers.each(function (i, container) {
            const $container = $(container);
            const offset = $container.offset();

            if (offset.bottom > $main.scrollTop()) {
                index = i;
                return false;
            }
        });

        const $userEntry = $userEntryTemplate.contents().clone();
        $userEntry.find(".username").text(`@${$.cookie("username") ?? "you"}`);
        $containers.eq(index).after($userEntry);
    });

    $(document).on("click", ".user-entry .user-info button", function () {
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid === 0) {
            signin();
        } else {
            profile();
        }
    });

    $(document).on("submit", ".user-entry .container-content", function (event) {
        event.preventDefault();

        const userid = parseInt($.cookie("userid") ?? 0);
        const $content = $(this);
        const $head = $content.find("[name='head']");
        const $body = $content.find("[name='body']");

        $head.removeClass("highlighted");
        $body.removeClass("highlighted");

        let valid = true;
        if (userid === 0) {
            signin();
            valid = false;
        }
        if (!validHead($head.val())) {
            $head.addClass("highlighted");
            valid = false;
        }
        if (!validBody($body.val())) {
            $body.addClass("highlighted");
            valid = false;
        }
        if (valid) {
            $.post("../php/post-actions/create.php", { head: $head.val(), body: $body.val() })
                .done(function () {
                    $head.val("");
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
