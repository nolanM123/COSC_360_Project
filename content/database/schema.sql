-- Drop tables if they exist
DROP TABLE IF EXISTS likes, shares, comments, posts, content, users;

-- Table for users
CREATE TABLE users (
    userid INT(11) NOT NULL AUTO_INCREMENT,
    role ENUM('user', 'moderator', 'admin', 'super-admin') NOT NULL DEFAULT 'user',
    email VARCHAR(255) NOT NULL,
    username VARCHAR(24) NOT NULL,
    password VARCHAR(255) NOT NULL,
    joindate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (userid)
);

-- Indexes for the users table
-- CREATE INDEX idx_user_role ON users(role);
-- CREATE INDEX idx_user_email ON users(email);
-- CREATE INDEX idx_user_username ON users(username);

-- Table for content
CREATE TABLE content (
    contentid INT(11) NOT NULL AUTO_INCREMENT,
    head VARCHAR(80),
    body VARCHAR(280),
    likes INT(11) DEFAULT 0,
    comments INT(11) DEFAULT 0,
    shares INT(11) DEFAULT 0,
    PRIMARY KEY (contentid)
);

-- Indexes for the content table
-- CREATE INDEX idx_content_likes ON content(likes);
-- CREATE INDEX idx_content_comments ON content(comments);
-- CREATE INDEX idx_content_shares ON content(shares);

-- Table for posts
CREATE TABLE posts (
    contentid INT(11) NOT NULL,
    userid INT(11) NOT NULL,
    creationdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (contentid, userid),
    FOREIGN KEY (contentid) REFERENCES content(contentid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Indexes for the posts table
-- CREATE INDEX idx_posts_creationdate ON posts(creationdate);

-- Table for comments
CREATE TABLE comments (
    commentid INT(11) NOT NULL AUTO_INCREMENT,
    contentid INT(11) NOT NULL,
    parentid INT(11) NOT NULL,
    userid INT(11) NOT NULL,
    creationdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (commentid),
    FOREIGN KEY (contentid) REFERENCES content(contentid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (parentid) REFERENCES content(contentid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Indexes for the comments table
-- CREATE INDEX idx_comments_creationdate ON comments(creationdate);

-- Table for likes
CREATE TABLE likes (
    contentid INT(11) NOT NULL,
    userid INT(11) NOT NULL,
    creationdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (contentid, userid),
    FOREIGN KEY (contentid) REFERENCES content(contentid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Indexes for the likes table
-- CREATE INDEX idx_likes_creationdate ON likes(creationdate);

-- Table for shares
CREATE TABLE shares (
    contentid INT(11) NOT NULL,
    userid INT(11) NOT NULL,
    creationdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (contentid, userid),
    FOREIGN KEY (contentid) REFERENCES content(contentid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Indexes for the shares table
-- CREATE INDEX idx_shares_creationdate ON shares(creationdate);