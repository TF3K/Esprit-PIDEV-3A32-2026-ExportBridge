package Services;

import DAO.ManagerDAO;
import DAO.SettingsDAO;
import Entities.Manager;
import Utils.AppState;
import Utils.PasswordUtil;
import Utils.SessionManager;


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

    public String authenticate(String email, String password, boolean rememberMe) throws SQLException {
        Manager manager = managerDAO.findByEmail(email);

        if (manager == null) {
            return null;
        }

        if (!PasswordUtil.verifyPw(password, manager.getPassword())) {
            return null;
        }

        managerDAO.updateLastLogin(manager.getId());

        String sessionId = SessionManager.createSession(manager.getId(), "localhost");

        AppState.setCurrentSessionId(sessionId);
        AppState.setCurrentManager(manager);

        if (rememberMe) {
            AppState.saveSessionToFile(sessionId);
        }

        return sessionId;
    }

    public Manager restoreSession(String sessionId) throws SQLException {
        if (!SessionManager.isValidSession(sessionId)) {
            return null;
        }

        Long managerId = SessionManager.getManagerIdFromSession(sessionId);
        if (managerId == null) {
            return null;
        }

        Manager manager = managerDAO.findById(managerId);
        if (manager != null) {
            SessionManager.updateLastAccess(sessionId);

            AppState.setCurrentSessionId(sessionId);
            AppState.setCurrentManager(manager);
        }

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

    public void logout(String sessionId) {
        if (sessionId != null) {
            SessionManager.invalidateSession(sessionId);
        }
        AppState.clearSession();
    }
}
