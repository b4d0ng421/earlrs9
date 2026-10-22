-- =========================================================
-- RS8 Racing — remove data created by tests/earlrs9.side
-- Local development database only.
--
-- Orders are removed automatically because users.id is the parent
-- of orders.user_id with ON DELETE CASCADE.
-- =========================================================

USE rs8_racing;

DELETE FROM contact_messages
WHERE email LIKE 'sel\_contact\_%';

DELETE FROM users
WHERE username LIKE 'sel\_qa\_%'
   OR username LIKE 'sel\_buyer\_%';
