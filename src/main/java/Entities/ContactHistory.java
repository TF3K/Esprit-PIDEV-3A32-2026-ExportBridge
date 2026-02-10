package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class ContactHistory {
    private Long id;
    private Long sourceCompanyId;
    private Long targetCompanyId;
    private LocalDateTime contactDate;
    private String contactType;
    private String notes;
    private Long contactedByManagerId;
}