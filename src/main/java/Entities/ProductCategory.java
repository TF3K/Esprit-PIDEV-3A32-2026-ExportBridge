package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class ProductCategory {
    private Long id;
    private String name;
    private String description;
    private String slug;

    // Predefined common categories (keeps backward compatibility with enum-like
    // usage)
    private static final Map<String, ProductCategory> VALUES = new ConcurrentHashMap<>();

    public static final ProductCategory AGRICULTURAL = register("AGRICULTURAL");
    public static final ProductCategory FOOD_BEVERAGE = register("FOOD_BEVERAGE");
    public static final ProductCategory TEXTILES = register("TEXTILES");
    public static final ProductCategory ELECTRONICS = register("ELECTRONICS");
    public static final ProductCategory MACHINERY = register("MACHINERY");
    public static final ProductCategory CHEMICALS = register("CHEMICALS");
    public static final ProductCategory COSMETICS = register("COSMETICS");
    public static final ProductCategory MEDICAL_DEVICES = register("MEDICAL_DEVICES");
    public static final ProductCategory TOYS = register("TOYS");
    public static final ProductCategory HANDICRAFTS = register("HANDICRAFTS");
    public static final ProductCategory OLIVE_OIL = register("OLIVE_OIL");
    public static final ProductCategory DATES = register("DATES");
    public static final ProductCategory SEAFOOD = register("SEAFOOD");
    public static final ProductCategory PHARMACEUTICAL = register("PHARMACEUTICAL");
    public static final ProductCategory AUTOMOTIVE_PARTS = register("AUTOMOTIVE_PARTS");
    public static final ProductCategory FURNITURE = register("FURNITURE");
    public static final ProductCategory LEATHER_GOODS = register("LEATHER_GOODS");
    public static final ProductCategory CERAMICS = register("CERAMICS");
    public static final ProductCategory OTHER = register("OTHER");

    private static ProductCategory register(String name) {
        ProductCategory pc = new ProductCategory(null, name, null, name.toLowerCase());
        // populate a sensible default description for predefined categories
        pc.setDescription(defaultDescriptionFor(name));
        VALUES.put(name, pc);
        return pc;
    }

    private static String defaultDescriptionFor(String name) {
        return switch (name) {
            case "AGRICULTURAL" -> "Raw and processed agricultural products";
            case "FOOD_BEVERAGE" -> "Food and beverage items for retail and wholesale";
            case "TEXTILES" -> "Textile and apparel products";
            case "ELECTRONICS" -> "Electronic devices and components";
            case "MACHINERY" -> "Industrial and manufacturing machinery";
            case "CHEMICALS" -> "Industrial and specialty chemicals";
            case "COSMETICS" -> "Beauty and personal care products";
            case "MEDICAL_DEVICES" -> "Medical instruments and devices";
            case "TOYS" -> "Children's toys and games";
            case "HANDICRAFTS" -> "Handmade crafts and artisanal goods";
            case "OLIVE_OIL" -> "Extra virgin and other olive oil products";
            case "DATES" -> "Fresh and processed dates";
            case "SEAFOOD" -> "Fresh and frozen seafood products";
            case "PHARMACEUTICAL" -> "Pharmaceutical products and medicines";
            case "AUTOMOTIVE_PARTS" -> "Spare parts and components for vehicles";
            case "FURNITURE" -> "Home and office furniture";
            case "LEATHER_GOODS" -> "Leather apparel and accessories";
            case "CERAMICS" -> "Ceramic goods and pottery";
            default -> "Miscellaneous products";
        };
    }

    // Mimic enum API
    public static ProductCategory valueOf(String name) {
        if (name == null)
            return null;
        ProductCategory pc = VALUES.get(name);
        if (pc != null)
            return pc;
        // fallback: create a dynamic category with given name
        ProductCategory dyn = new ProductCategory(null, name, null, name.toLowerCase());
        VALUES.put(name, dyn);
        return dyn;
    }

    public String name() {
        return this.name;
    }

    // Support enum-like iteration
    public static ProductCategory[] values() {
        return VALUES.values().toArray(new ProductCategory[0]);
    }
}
