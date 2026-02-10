package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class Product {
    private Long id;
    private Long companyId;
    private String name;
    private String hsCode;
    private String description;
    private Double quantity;
    private String unit;
    private Double unitPrice;
    private String currency;
    private String originCriteria;

    public Double getTotalValue() {
        if (quantity == null || unitPrice == null) return 0.0;
        return quantity * unitPrice;
    }
}
