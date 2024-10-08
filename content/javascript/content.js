import { confirm, signin } from "../javascript/popups.js";

const userid = $.cookie("userid");
const username = $.cookie("username");

const $userPostTemplate = $(await $.get("./templates/user-post.html"));
const $postUsername = $userPostTemplate.contents().find(".user-reply .user-info .username");
const $postUsericon = $userPostTemplate.contents().find(".user-reply .user-info button img");

const $userCommentTemplate = $(await $.get("./templates/user-comment.html"));
const $commentUsername = $userCommentTemplate.contents().find(".user-reply .user-info .username");
const $commentUsericon = $userCommentTemplate.contents().find(".user-reply .user-info button img");

if (userid) {
    $postUsername.text(`@${username}`);
    $postUsericon.attr("src", `../images/user-icons/user-${$.cookie("userid")}-icon.png`)
    $commentUsername.text(`@${username}`);
    $commentUsericon.attr("src", `../images/user-icons/user-${$.cookie("userid")}-icon.png`)
} else {
    $postUsername.text("@you");
    $postUsericon.attr("src", "../images/icons/profile-icon.svg");
    $commentUsername.text("@you");
    $commentUsericon.attr("src", "../images/icons/profile-icon.svg")
}

$(document).ready(function () {
    const $main = $("main");
    loadContent($main);

    $main.on("scroll", function () {
        if ($main.scrollTop() + $main.innerHeight() === $main.prop("scrollHeight")) {
            loadContent($main);
        }
    });

    $("#search-form").on("submit", function (event) {
        event.preventDefault();

        const $this = $(this);
        $this.find("[name='timestamp1']").val("invalid");
        $this.find("[name='timestamp2']").val("invalid");
        $this.find("[name='terms']").val($this.find("[name='search-terms']").val());
        $this.find("[name='type']").val($this.find("[name='search-type']").val());

        const $main = $("main");
        $main.children().not("header").remove();
        $main.scroll();
    });
});

function loadContent($root) {
    const $searchForm = $("#search-form");
    const $timestamp1 = $searchForm.find("[name='timestamp1']");
    const $timestamp2 = $searchForm.find("[name='timestamp2']");
    const $terms = $searchForm.find("[name='terms']");
    const $type = $searchForm.find("[name='type']");

    let url, terms, data, getContent;
    data = {
        timestamp1: $timestamp1.val(),
        timestamp2: $timestamp2.val(),
    }

    switch ($type.val()) {
        case "users":
            url = "../php/post-actions/get.php";
            data.username = $terms.val();
            getContent = getPost;
            break;
        
        case "posts":
            url = "../php/post-actions/get.php";
            terms = $terms.val().toLowerCase().match(/\b\w+\b/g);
            terms = Array.from(new Set(terms));
            terms = JSON.stringify(terms);
            data.terms = terms;
            getContent = getPost;
            break;
    
        case "comments":
            url = "../php/comment-actions/get.php";
            terms = $terms.val().toLowerCase().match(/\b\w+\b/g);
            terms = Array.from(new Set(terms));
            terms = JSON.stringify(terms);
            data.terms = terms;
            getContent = getComment;
            break;
    }

    $.get(url, data)
        .done(function (response) {
            JSON.parse(response).forEach(function (info) {
                boundTimestamps($timestamp1, $timestamp2, info.creationdate);
                $root.append(getContent(info));
            });
        });
}

export function getPost(info) {
    const $post = $userPostTemplate.contents().clone();
    setContent($post, info);

    $post.find(".like-button").on("click", function () {
        const $this = $(this);
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid) {
            $.post("../php/like-actions/toggle.php", { contentid: info.contentid })
                .done(function (response) {
                    const $likeLabel = $this.find(".like-count");
                    if (response === "Liked") {
                        $this.addClass("selected");
                        $likeLabel.text(parseInt($likeLabel.text()) + 1);
                    } else {
                        $this.removeClass("selected");
                        $likeLabel.text(parseInt($likeLabel.text()) - 1);
                    }
                });
        } else {
            signin();
        }
    });

    $post.find(".comment-button").on("click", function () {
        $(this).closest(".user-post, .user-comment").find(">.user-reply, >.container-comments").toggle();
    });

    $post.find(".share-button").on("click", function () {
        const $this = $(this);
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid) {
            $.post("../php/share-actions/create.php", { contentid: info.contentid })
                .done(function (response) {
                    if (response !== "Share Created") {
                        return;
                    }

                    const $shareLabel = $this.find(".share-count");
                    $this.addClass("selected");
                    $shareLabel.text(parseInt($shareLabel.text()) + 1);
                });
        } else {
            signin();
        }
    });

    let head, body;

    $post.on("click", ">.container-buttons .edit-button", function () {
        const $this = $(this);
        $this.text("save");
        $this.addClass("selected");
        $this.addClass("save-button");
        $this.removeClass("edit-button");

        const $content =  $this.closest(".user-post").find(">.container-content");
        head = $content.find("[name='head']").val();
        body = $content.find("[name='body']").val();
        $content.find("input, textarea").prop("disabled", false);
    });

    $post.on("click", ">.container-buttons .save-button", async function () {
        const $this = $(this);
        $this.text("edit");
        $this.addClass("edit-button");
        $this.removeClass("selected");
        $this.removeClass("save-button");

        const $content =  $this.closest(".user-post").find(">.container-content");
        $content.find("input, textarea").prop("disabled", true);

        if (await confirm()) {
            head = $content.find("[name='head']").val();
            body = $content.find("[name='body']").val();

            $.post("../php/post-actions/modify.php", { contentid: info.contentid, head: head, body: body });
        } else {
            $content.find("[name='head']").val(head);
            $content.find("[name='body']").val(body);
        }
    });

    let id = setInterval(function () {
        $.get("../php/post-actions/get.php", { contentid: info.contentid })
            .done(function (response) {
                setContent($post, JSON.parse(response)[0]);
            });
    }, 10 ** 5);

    $post.find(".delete-button").on("click", async function () {
        if (!await confirm()) {
            return;
        }

        $.post("../php/post-actions/delete.php", { contentid: info.contentid })
            .done(function () {
                clearInterval(id);
                $post.remove();
            });
    });

    return $post;
}

export function getComment(info) {
    const $comment = $userCommentTemplate.contents().clone();
    setContent($comment, info);

    $comment.find(".like-button").on("click", function () {
        const $this = $(this);
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid) {
            $.post("../php/like-actions/toggle.php", { contentid: info.contentid })
                .done(function (response) {
                    const $likeLabel = $this.find(".like-count");
                    if (response === "Liked") {
                        $this.addClass("selected");
                        $likeLabel.text(parseInt($likeLabel.text()) + 1);
                    } else {
                        $this.removeClass("selected");
                        $likeLabel.text(parseInt($likeLabel.text()) - 1);
                    }
                });
        } else {
            signin();
        }
    });

    $comment.find(".comment-button").on("click", function () {
        $(this).closest(".user-post, .user-comment").find(">.user-reply, >.container-comments").toggle();
    });

    $comment.find(".share-button").on("click", function () {
        const $this = $(this);
        const userid = parseInt($.cookie("userid") ?? 0);

        if (userid) {
            $.post("../php/share-actions/create.php", { contentid: info.contentid })
                .done(function (response) {
                    if (response !== "Share Created") {
                        return;
                    }

                    const $shareLabel = $this.find(".share-count");
                    $this.addClass("selected");
                    $shareLabel.text(parseInt($shareLabel.text()) + 1);
                });
        } else {
            signin();
        }
    });

    let body;

    $comment.on("click", ">.container-buttons .edit-button", function () {
        const $this = $(this);
        $this.text("save");
        $this.addClass("selected");
        $this.addClass("save-button");
        $this.removeClass("edit-button");

        const $content =  $this.closest(".user-comment").find(">.container-content");
        body = $content.find("[name='body']").val();
        $content.find("input, textarea").prop("disabled", false);
    });

    $comment.on("click", ">.container-buttons .save-button", async function () {
        const $this = $(this);
        $this.text("edit");
        $this.addClass("edit-button");
        $this.removeClass("selected");
        $this.removeClass("save-button");

        const $content =  $this.closest(".user-comment").find(">.container-content");
        $content.find("input, textarea").prop("disabled", true);

        if (await confirm()) {
            body = $content.find("[name='body']").val();

            $.post("../php/comment-actions/modify.php", { contentid: info.contentid, body: body });
        } else {
            $content.find("[name='body']").val(body);
        }
    });

    let id = setInterval(function () {
        $.get("../php/comment-actions/get.php", { contentid: info.contentid })
            .done(function (response) {
                setContent($comment, JSON.parse(response)[0]);
            });
    }, 10 ** 5);

    $comment.find(".delete-button").on("click", async function () {
        if (!await confirm()) {
            return;
        }

        $.post("../php/comment-actions/delete.php", { contentid: info.contentid })
            .done(function () {
                clearInterval(id);
                $comment.remove();
            });
    });

    return $comment;
}

export function setContent($post, info) {
    const userid = parseInt($.cookie("userid") ?? 0);
    const username = $.cookie("username");

    const $userInfo = $post.find(">.user-info");
    $userInfo.find("button img").attr("src", `../images/user-icons/user-${info.userid}-icon.png`);
    $userInfo.find(".username").text(`@${info.username}`);

    const $content = $post.find(">.container-content");
    $content.find("[name='userid']").val(info.userid);
    $content.find("[name='contentid']").val(info.contentid);
    $content.find("[name='head']").val(info.head);
    $content.find("[name='body']").val(info.body);

    const $buttons = $post.find(">.container-buttons");
    const $actions = $buttons.find(".container-actions");
    const $likeButton = $actions.find(".like-button");
    const $commentButton = $actions.find(".comment-button");
    const $shareButton = $actions.find(".share-button");
    $likeButton.find(".like-count").text(info.likes);
    $commentButton.find(".comment-count").text(info.comments);
    $shareButton.find(".share-count").text(info.shares);

    if (userid !== 0) {
        $.get("../php/user-actions/has.php", { userid: userid, contentid: info.contentid })
            .done(function (response) {
                let has = JSON.parse(response);
                if (has.liked) {
                    $likeButton.addClass("selected");
                } else {
                    $likeButton.removeClass("selected");
                }
                if (has.commented) {
                    $commentButton.addClass("selected");
                } else {
                    $commentButton.removeClass("selected");
                }
                if (has.shared) {
                    $shareButton.addClass("selected");
                } else {
                    $shareButton.removeClass("selected");
                }
            });
    }

    if (info.role === "admin" || info.userid === userid) {
        $buttons.find(".container-controls").show();
    } else {
        $buttons.find(".container-controls").hide();
    }

    const $userReply = $post.find(">.user-reply");
    $userReply.find(".container-content [name='parentid']").val(info.contentid);

    const $replyUserInfo = $userReply.find(".user-info");
    if (userid) {
        $replyUserInfo.find(".username").text(`@${username}`);
        $replyUserInfo.find("img").attr("src", `../images/user-icons/user-${userid}-icon.png`);
    } else {
        $replyUserInfo.find(".username").text("@you");
        $replyUserInfo.find("img").attr("src", "../images/icons/profile-icon.svg")
    }

    $post.find(">.container-comments>.container-content [name='parentid']").val(info.contentid);
}

export function boundTimestamps($timestamp1, $timestamp2, creationdate) {
    const date1 = new Date($timestamp1.val());
    const date2 = new Date($timestamp2.val());
    const datec = new Date(creationdate);

    if (datec < date1 || isNaN(date1)) {
        $timestamp1.val(creationdate);
    }
    if (datec > date2 || isNaN(date2)) {
        $timestamp2.val(creationdate);
    }
}