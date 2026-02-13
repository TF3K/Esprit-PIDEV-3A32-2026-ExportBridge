package Services;

import DAO.ManagerDAO;
import DAO.SettingsDAO;
import Entities.Manager;
import Utils.PasswordUtil;


import java.sql.SQLException;
import java.time.LocalDateTime;

public class AuthenticationService {
    private final SettingsDAO settingsDAO;
    private final ManagerDAO managerDAO;

    public AuthenticationService() {
        this.managerDAO = new ManagerDAO();
        this.settingsDAO = new SettingsDAO();
    }

    public Manager register(String firstName, String lastName, String email, String password, Long companyId) throws SQLException {
        if (firstName == null || firstName.trim().isEmpty()) {
            throw new IllegalArgumentException("First name is required");
        }

        if (lastName == null || lastName.trim().isEmpty()) {
            throw new IllegalArgumentException("Last name is required");
        }

        if (email == null || !isValidEmail(email)) {
            throw new IllegalArgumentException("Valid email is required");
        }

        if (password == null || password.length() < 8) {
            throw new IllegalArgumentException("Password must be at least 8 characters");
        }

        if (managerDAO.emailExists(email)) {
            throw new IllegalArgumentException("Email already registered");
        }

        String hashedPassword = PasswordUtil.hashPassword(password);
        Manager manager = Manager.builder()
                .firstName(firstName)
                .lastName(lastName)
                .email(email)
                .password(hashedPassword)
                .companyId(companyId)
                .createdAt(LocalDateTime.now())
                .build();

        Manager created = managerDAO.create(manager);
        settingsDAO.createDefaultSettings(created.getId());

        created.setPassword(null);
        return created;
    }

    public Manager authenticate(String email, String password) throws SQLException {
        if (email == null || password == null) {
            return null;
        }
        Manager manager = managerDAO.findByEmail(email);

        if (manager == null) return null;

        boolean passwordMatches = PasswordUtil.verifyPw(password, manager.getPassword());

        if (!passwordMatches) return null;


        managerDAO.updateLastLogin(manager.getId());
        manager.setLastLogin(LocalDateTime.now());

        return manager;
    }

    public boolean changePassword(Long managerId, String oldPassword, String newPassword) throws SQLException {
        if (newPassword == null || newPassword.length() < 8 ) {
            throw new IllegalArgumentException("New password must be at least 8 characters long");
        }

        Manager manager = managerDAO.findById(managerId);

        if (manager == null) return false;

        if (!PasswordUtil.verifyPw(oldPassword, manager.getPassword())) return false;

        String hashedPassword = PasswordUtil.hashPassword(newPassword);
        manager.setPassword(hashedPassword);

        return managerDAO.update(manager);
    }

    public Manager getManagerById(Long id) throws SQLException {
        Manager manager = managerDAO.findById(id);

        if (manager != null) manager.setPassword(null);
        return manager;
    }

    private boolean isValidEmail(String email) {
        return email.matches("^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$");
    }
}
