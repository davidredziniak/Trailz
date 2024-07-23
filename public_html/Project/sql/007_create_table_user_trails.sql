CREATE TABLE IF NOT EXISTS `User_Trails` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`user_id` INT NOT NULL,
	`trail_id` INT NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY (trail_id) REFERENCES Trails(id),
	FOREIGN KEY (user_id) REFERENCES Users(id)
)