package Services;

import DAO.PartnershipDAO;
import Entities.Partnership;
import Entities.PartnershipStatus;
import Entities.PartnershipType;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.util.List;

public class PartnershipService {
    private final PartnershipDAO partnershipDAO;

    public PartnershipService() {
        this.partnershipDAO = new PartnershipDAO();
    }

    public Partnership createPartnership(Long companyId, PartnershipType type, String notes) throws SQLException {

        if (companyId == null) {
            throw new IllegalArgumentException("Company is required");
        }

        Partnership existing = partnershipDAO.findByCompanies(companyId, companyId);
        if (existing != null && existing.getStatus() != PartnershipStatus.TERMINATED) {
            throw new IllegalArgumentException("Partnership already exists for this company");
        }

        Partnership partnership = new Partnership();
        partnership.setSourceCompanyId(companyId);
        partnership.setStatus(PartnershipStatus.PENDING);
        partnership.setType(type);
        partnership.setNotes(notes);

        return partnershipDAO.create(partnership);
    }

    public Partnership getPartnershipById(Long id) throws SQLException {
        return partnershipDAO.findById(id);
    }

    public List<Partnership> getCompanyPartnerships(Long companyId) throws SQLException {
        return partnershipDAO.findByCompanyId(companyId);
    }

    public List<Partnership> getActivePartnerships(Long companyId) throws SQLException {
        return partnershipDAO.findActiveByCompanyId(companyId);
    }

    public boolean activatePartnership(Long partnershipId) throws SQLException {
        Partnership partnership = partnershipDAO.findById(partnershipId);
        if (partnership == null) {
            throw new IllegalArgumentException("Partnership not found");
        }

        partnership.setStatus(PartnershipStatus.ACTIVE);
        partnership.setEstablishedDate(LocalDateTime.now());

        return partnershipDAO.update(partnership);
    }

    public boolean terminatePartnership(Long partnershipId, String reason) throws SQLException {
        Partnership partnership = partnershipDAO.findById(partnershipId);
        if (partnership == null) {
            throw new IllegalArgumentException("Partnership not found");
        }

        partnership.setStatus(PartnershipStatus.TERMINATED);
        partnership.setTerminatedDate(LocalDateTime.now());
        if (reason != null) {
            partnership.setNotes(partnership.getNotes() + "\nTermination reason: " + reason);
        }

        return partnershipDAO.update(partnership);
    }

    public boolean updatePartnership(Partnership partnership) throws SQLException {
        if (partnership.getId() == null) {
            throw new IllegalArgumentException("Partnership ID is required");
        }

        return partnershipDAO.update(partnership);
    }

    public boolean deletePartnership(Long partnershipId) throws SQLException {
        return partnershipDAO.delete(partnershipId);
    }

    public long countCompanyPartnerships(Long companyId) throws SQLException {
        return partnershipDAO.countByCompanyId(companyId);
    }
}