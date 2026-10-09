START TRANSACTION;
INSERT INTO `user` (`login`, `name`, `pwd_hash`)
VALUES (
    'VKa',
    'Канунников Владимир Викторович',
    '$2y$12$A2RsJetG58ExTI4v6BAWR.dVS.Jj5TjkY.932mABUOJ.7VLm8RjE.'
);

INSERT INTO `operation` (`login`, `operation`, `x`, `y`, `z`)
VALUES
    ('VKa', 'plus', 10, 5, 15),
    ('VKa', 'minus', 20, 8, 12),
    ('VKa', 'multiply', 7, 6, 42);

COMMIT;
