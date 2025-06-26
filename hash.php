<?php
//echo password_hash("admin123", PASSWORD_DEFAULT);
// Hasilnya akan seperti: $2y$10$xyzabc... (copy ini untuk password admin)

echo password_hash("admin123", PASSWORD_DEFAULT);
// Hasilnya akan seperti: $2y$10$defghi... (copy ini untuk password karyawan)
?>