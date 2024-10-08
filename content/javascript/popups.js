import { validEmail, validUsername, validPassword } from "../javascript/modules/authenticate.js"

const $confirmTemplate = $(await $.get("./templates/popup-confirm.html"));
const $profileTemplate = $(await $.get("./templates/popup-profile.html"));
const $signinTemplate = $(await $.get("./templates/popup-signin.html"));
const $signupTemplate = $(await $.get("./templates/popup-signup.html"));

$(document).ready(function () {
    $(document).on("click", ".popup", function (event) {
        if (event.currentTarget === event.target) {
            $(this).remove();
        }
    });

    $(document).on("click", ".user-profile .popup-buttons [type='button']", function () {
        const $this = $(this);

        $.post("../php/user-actions/signout.php")
            .done(function () {
                $(".user-info .username").not(".user-post>.user-info .username, .user-comment>.user-info .username").text("@you");
                $(".user-info img").not(".user-post>.user-info img, .user-comment>.user-info img").attr("src", "../images/icons/profile-icon.svg");
                signin($this.closest(".popup").parent());
            });
    });

    $(document).on("submit", ".user-profile-form", async function (event) {
        event.preventDefault();

        const data = new FormData();
        const $content = $(this);
        const $usericon = $content.find("[name='usericon']");
        const $email = $content.find("[name='email']");
        const $username = $content.find("[name='username']");
        const $password = $content.find("[name='password']");
        const $repassword = $content.find("[name='repassword']");

        $email.removeClass("highlighted");
        $username.removeClass("highlighted");
        $password.removeClass("highlighted");
        $repassword.removeClass("highlighted");

        let valid = true;
        if ($password.val().length !== 0) {
            if ($password.val() !== $repassword.val()) {
                $password.addClass("highlighted");
                $repassword.addClass("highlighted");
                valid = false;
            }
            if (!validPassword($password.val())) {
                $password.addClass("highlighted");
                valid = false;
            }
            if (valid) {
                data.append("password", $password.val());
            }
        }
        if ($username.val().length !== 0) {
            if (!validUsername($username.val())) {
                $username.addClass("highlighted");
                valid = false;
            } else {
                data.append("username", $username.val());
            }
        }
        if ($email.val().length !== 0) {
            if (!validEmail($email.val())) {
                $email.addClass("highlighted");
                valid = false;
            } else {
                data.append("email", $email.val());
            }
        }
        if ($usericon.get(0).files.length !== 0) {
            data.append("usericon", $usericon.get(0).files[0]);
        }
        if (valid && await confirm()) {
            $.ajax({
                url: "../php/user-actions/modify.php",
                type: "POST",
                data: data,
                contentType: false,
                processData: false,
                success: function() {
                    $(".user-info .username").not(".user-post>.user-info .username, .user-comment>.user-info .username").text(`@${$.cookie("username")}`);
                    $(".user-info img").not(".user-post>.user-info img, .user-comment>.user-info img").attr("src", `../images/user-icons/user-${$.cookie("userid")}-icon.png`);
                    $content.closest(".popup").remove();
                }
            });
        }
    });

    $(document).on("submit", ".user-signin-form", function (event) {
        event.preventDefault();

        const $content = $(this);
        const $identity = $content.find("[name='identity']");
        const $password = $content.find("[name='password']");

        $identity.removeClass("highlighted");
        $password.removeClass("highlighted");

        let valid = true;
        if (!validPassword($password.val())) {
            $password.addClass("highlighted");
            valid = false;
        }
        if (!validEmail($identity.val()) && !validUsername($identity.val())) {
            $identity.addClass("highlighted");
            valid = false;
        }
        if (valid) {
            $.post("../php/user-actions/signin.php", { identity: $identity.val(), password: $password.val() })
                .done(function () {
                    $(".user-info .username").not(".user-post>.user-info .username, .user-comment>.user-info .username").text(`@${$.cookie("username")}`);
                    $(".user-info img").not(".user-post>.user-info img, .user-comment>.user-info img").attr("src", `../images/user-icons/user-${$.cookie("userid")}-icon.png`);
                    $content.closest(".popup").remove();
                })
                .fail(function (xhr) {
                    
                });
        }
    });

    $(document).on("click", ".user-signin .popup-buttons [type='button']", function () {
        signup($(this).closest(".popup").parent());
    });

    $(document).on("submit", ".user-signup-form", function (event) {
        event.preventDefault();

        const $content = $(this);
        const $email = $content.find("[name='email']");
        const $username = $content.find("[name='username']");
        const $password = $content.find("[name='password']");
        const $repassword = $content.find("[name='repassword']");

        $email.removeClass("highlighted");
        $username.removeClass("highlighted");
        $password.removeClass("highlighted");
        $repassword.removeClass("highlighted");

        let valid = true;
        if ($password.val() !== $repassword.val()) {
            $password.addClass("highlighted");
            $repassword.addClass("highlighted");
            valid = false;
        }
        if (!validPassword($password.val())) {
            $password.addClass("highlighted");
            valid = false;
        }
        if (!validUsername($username.val())) {
            $username.addClass("highlighted");
            valid = false;
        }
        if (!validEmail($email.val())) {
            $email.addClass("highlighted");
            valid = false;
        }
        if (valid) {
            $.post("../php/user-actions/signup.php", { email: $email.val(), username: $username.val(), password: $password.val() })
                .done(function () {
                    signin($content.closest(".popup").parent());
                })
                .fail(function (xhr) {

                });
        }
    });

    $(document).on("click", ".user-signup .popup-buttons [type='button']", function () {
        signin($(this).closest(".popup").parent());
    });
});

export function confirm($root) {
    if (typeof $root === "undefined") {
        $root = $("body");
    }

    const $confirm = $confirmTemplate.contents().clone();
    $root.append($confirm);

    return new Promise(function (reslove) {
        const $popupBtns = $confirm.find(".container .popup-buttons");

        $popupBtns.on("click", "[type='submit']", function () {
            $confirm.remove();
            reslove(true)
        });

        $popupBtns.on("click", "[type='button']", function () {
            $confirm.remove();
            reslove(false)
        });
    });
}

export function profile($root) {
    if (typeof $root === "undefined") {
        $root = $("body");
    }

    $root.find(".popup").remove();

    $root.append($profileTemplate.contents().clone());
}

export function signin($root) {
    if (typeof $root === "undefined") {
        $root = $("body");
    }

    $root.find(".popup").remove();

    $root.append($signinTemplate.contents().clone());
}

export function signup($root) {
    if (typeof $root === "undefined") {
        $root = $("body");
    }

    $root.find(".popup").remove();

    $root.append($signupTemplate.contents().clone());
}