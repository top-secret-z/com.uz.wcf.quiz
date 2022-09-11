-- Data in user table
ALTER TABLE wcf1_user ADD uzQuiz INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD uzQuizRate FLOAT(5,2) NOT NULL DEFAULT 0.00;
ALTER TABLE wcf1_user ADD uzQuizPlayed TEXT;

-- Quiz
DROP TABLE IF EXISTS wcf1_quiz;
CREATE TABLE wcf1_quiz (
	quizID				INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	assignGroupIDs		TEXT NOT NULL,
	assignResult		TINYINT(3) DEFAULT 90,
	counter				INT(10) NOT NULL DEFAULT 0,
	groupIDs			TEXT NOT NULL,
	hasPeriod			TINYINT(1) NOT NULL DEFAULT 0,
	image 				VARCHAR(255) NOT NULL DEFAULT '',
	isActive			TINYINT(1) NOT NULL DEFAULT 1,
	playAgain			TINYINT(1) NOT NULL DEFAULT 1,
	periodEnd			INT(10) NOT NULL DEFAULT 0,
	periodStart			INT(10) NOT NULL DEFAULT 0,
	points				INT(10) NOT NULL DEFAULT 1,
	questions			INT(10) NOT NULL DEFAULT 0,
	randomize			TINYINT(1) NOT NULL DEFAULT 0,
	rating				INT(10) NOT NULL DEFAULT 0,
	ratingCount			INT(10) NOT NULL DEFAULT 0,
	ratingTotal			FLOAT(4,2) NOT NULL DEFAULT 0.00,
	showComment			TINYINT(1) NOT NULL DEFAULT 0,
	showOrder			INT(10) NOT NULL DEFAULT 99,
	showCorrect			TINYINT(1) NOT NULL DEFAULT 1,
	showResult			TINYINT(1) NOT NULL DEFAULT 1,
	showResultButton	TINYINT(1) NOT NULL DEFAULT 1,
	timeLimit			INT(10) NOT NULL DEFAULT 0,
	time				INT(10) NOT NULL,
	title				VARCHAR(255) NOT NULL,
	text				TEXT NOT NULL,
	userID				INT(10),
	username			VARCHAR(255) NOT NULL,
	paused				INT(10) NOT NULL DEFAULT 0,
	showStats			TINYINT(1) NOT NULL DEFAULT 1,
	showBest			TINYINT(1) NOT NULL DEFAULT 1,
	
	KEY (isActive),
	KEY (userID),
	KEY (paused),
);

DROP TABLE IF EXISTS wcf1_quiz_question;
CREATE TABLE wcf1_quiz_question (
	questionID		INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	categoryID		INT(10),
	question		TEXT NOT NULL,
	answerOne		TEXT NOT NULL,
	answerTwo		TEXT NOT NULL,
	answerThree		TEXT,
	answerFour		TEXT,
	answerFive		TEXT,
	answerSix		TEXT,
	comment			TEXT,
	count			TINYINT(1) NOT NULL,
	correct			TINYINT(1) NOT NULL DEFAULT 1,
	image 			VARCHAR(255) NOT NULL DEFAULT '',
	userID			INT(10),
	username		VARCHAR(255) NOT NULL DEFAULT '',
	isACP			TINYINT(1) NOT NULL DEFAULT 1,
	time			INT(10) NOT NULL,
	approved		TINYINT(1) NOT NULL DEFAULT 0,
	editedBy		VARCHAR(255) DEFAULT '',
	
	KEY (userID),
	KEY (isACP),
	KEY (approved)
);

DROP TABLE IF EXISTS wcf1_quiz_to_question;
CREATE TABLE wcf1_quiz_to_question (
	fakeID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quizID			INT(10) NOT NULL,
	questionID		INT(10) NOT NULL
);

DROP TABLE IF EXISTS wcf1_quiz_to_user;
CREATE TABLE wcf1_quiz_to_user (
	fakeID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quizID			INT(10) NOT NULL,
	userID			INT(10) NOT NULL,
	total			INT(10) NOT NULL,
	correct			INT(10) NOT NULL,
	time			INT(10) NOT NULL
);

DROP TABLE IF EXISTS wcf1_quiz_result;
CREATE TABLE wcf1_quiz_result (
	resultID		INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quizID			INT(10) NOT NULL,
	quizTitle		VARCHAR(255) NOT NULL,
	userID			INT(10) NOT NULL,
	username		VARCHAR(255) NOT NULL DEFAULT '',
	result		 	FLOAT(5,2) NOT NULL DEFAULT 0.00,
	time			INT(10) NOT NULL
);

DROP TABLE IF EXISTS wcf1_quiz_to_user_comment;
CREATE TABLE wcf1_quiz_to_user_comment (
	fakeID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quizID			INT(10) NOT NULL,
	userID			INT(10) NOT NULL,
	displayOnce		TINYINT(1) NOT NULL DEFAULT 0,
	displayed		TINYINT(1) NOT NULL DEFAULT 0,
	time			INT(10) NOT NULL,
	comment			TEXT NOT NULL
);

DROP TABLE IF EXISTS wcf1_quiz_rating;
CREATE TABLE wcf1_quiz_rating (
	ratingID		INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	quizID			INT(10) NOT NULL,
	userID			INT(10) NOT NULL,
	time			INT(10) NOT NULL,
	rating			TINYINT(1) NOT NULL,
	
	KEY quizID (quizID)
);

DROP TABLE IF EXISTS wcf1_quiz_temp;
CREATE TABLE wcf1_quiz_temp (
	uniqueID		VARCHAR(100) NOT NULL DEFAULT '',
	time			INT(10) NOT NULL DEFAULT 0,
	quizID			INT(10) NOT NULL,
	correct			INT(10) NOT NULL DEFAULT 0
);

ALTER TABLE wcf1_quiz_question ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

ALTER TABLE wcf1_quiz_to_question ADD FOREIGN KEY (quizID) REFERENCES wcf1_quiz (quizID) ON DELETE CASCADE;
ALTER TABLE wcf1_quiz_to_question ADD FOREIGN KEY (questionID) REFERENCES wcf1_quiz_question (questionID) ON DELETE CASCADE;

ALTER TABLE wcf1_quiz_to_user ADD FOREIGN KEY (quizID) REFERENCES wcf1_quiz (quizID) ON DELETE CASCADE;
ALTER TABLE wcf1_quiz_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_quiz_result ADD FOREIGN KEY (quizID) REFERENCES wcf1_quiz (quizID) ON DELETE CASCADE;
ALTER TABLE wcf1_quiz_result ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_quiz_to_user_comment ADD FOREIGN KEY (quizID) REFERENCES wcf1_quiz (quizID) ON DELETE CASCADE;
ALTER TABLE wcf1_quiz_to_user_comment ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_quiz_rating ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_quiz_rating ADD FOREIGN KEY (quizID) REFERENCES wcf1_quiz (quizID) ON DELETE CASCADE;

ALTER TABLE wcf1_quiz_temp ADD FOREIGN KEY (quizID) REFERENCES wcf1_quiz (quizID) ON DELETE CASCADE;
