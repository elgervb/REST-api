BEGIN TRANSACTION;
  CREATE TABLE "user" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "guid" TEXT NOT NULL,
    "username" TEXT NOT NULL,
    "password" TEXT NOT NULL,
    "active" INTEGER NOT NULL DEFAULT (1),
    "ip" TEXT,
    "full_name" TEXT,
    "email" TEXT NOT NULL,
    "timestamp" TIMESTAMP default CURRENT_TIMESTAMP,
    "admin" INTEGER NOT NULL DEFAULT (0),
    "activation" TEXT,
    "context" TEXT
  );
  CREATE UNIQUE INDEX "uq_guid" on user (guid ASC);
  CREATE UNIQUE INDEX "uq_username" on user (username ASC);
  INSERT INTO "user" VALUES(1, "0ab61594-f9d7-4d11-8a57-56ded1153810","admin","d033e22ae348aeb5660fc2140aec35850c4da997",1,"127.0.0.1","Administrator","adin@admin.com",1407503963201,1);
COMMIT;