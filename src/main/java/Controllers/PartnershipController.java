package Controllers;

import Entities.Partnership;
import Entities.PartnershipType;
import Services.PartnershipService;

import java.sql.SQLException;
import java.util.List;

public class PartnershipController {
    private final PartnershipService partnershipService;

    public PartnershipController() {
        this.partnershipService = new PartnershipService();
    }

    public Partnership createPartnership(Long companyId, PartnershipType type, String notes) {
        try {
            return partnershipService.createPartnership(companyId, type, notes);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    // Backwards-compatible overload for callers that still provide source+target
    // company IDs.
    public Partnership createPartnership(Long sourceCompanyId, Long targetCompanyId, PartnershipType type,
            String notes) {
        try {
            // Use sourceCompanyId as the representative companyId in the new model
            return partnershipService.createPartnership(sourceCompanyId, type, notes);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    public Partnership getPartnership(Long id) {
        try {
            return partnershipService.getPartnershipById(id);
        } catch (SQLException e) {
            System.err.println("Error fetching partnership: " + e.getMessage());
            return null;
        }
    }

    public List<Partnership> getCompanyPartnerships(Long companyId) {
        try {
            return partnershipService.getCompanyPartnerships(companyId);
        } catch (SQLException e) {
            System.err.println("Error fetching partnerships: " + e.getMessage());
            return List.of();
        }
    }

    public List<Partnership> getActivePartnerships(Long companyId) {
        try {
            return partnershipService.getActivePartnerships(companyId);
        } catch (SQLException e) {
            System.err.println("Error fetching active partnerships: " + e.getMessage());
            return List.of();
        }
    }

    public boolean activatePartnership(Long partnershipId) {
        try {
            return partnershipService.activatePartnership(partnershipId);
        } catch (SQLException e) {
            System.err.println("Error activating partnership: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean terminatePartnership(Long partnershipId, String reason) {
        try {
            return partnershipService.terminatePartnership(partnershipId, reason);
        } catch (SQLException e) {
            System.err.println("Error terminating partnership: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean updatePartnership(Partnership partnership) {
        try {
            return partnershipService.updatePartnership(partnership);
        } catch (SQLException e) {
            System.err.println("Error updating partnership: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean deletePartnership(Long partnershipId) {
        try {
            return partnershipService.deletePartnership(partnershipId);
        } catch (SQLException e) {
            System.err.println("Error deleting partnership: " + e.getMessage());
            return false;
        }
    }
}