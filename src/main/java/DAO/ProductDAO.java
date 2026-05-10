package DAO;

import Entities.Product;
import Entities.ProductCategory;
import DAO.ProductCategoryDAO;
import Utils.DatabasePlugin;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProductDAO implements GenericDAO<Product, Long> {

    @Override
    public Product create(Product product) throws SQLException {
        System.out.println("[DEBUG] ProductDAO.create called for product object: " + product);
        String sql = "INSERT INTO products (company_id, name, description, hs_code, " +
                "category_id, quantity, unit, unit_price, currency, origin_criteria, created_at, last_updated) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        ProductCategoryDAO pcDao = new ProductCategoryDAO();
        // ensure category exists and get id BEFORE preparing statement to avoid
        // reconnects
        Long categoryId = null;
        if (product.getCategory() != null) {
            if (product.getCategory().getId() != null) {
                categoryId = product.getCategory().getId();
            } else {
                ProductCategory found = pcDao.findByName(product.getCategory().getName());
                if (found != null)
                    categoryId = found.getId();
                else {
                    ProductCategory created = pcDao.create(product.getCategory());
                    categoryId = created.getId();
                    // set back id on product category
                    product.getCategory().setId(categoryId);
                }
            }
        }

        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            System.out.println("[DEBUG] Prepared statement created for INSERT; conn=" + conn);

            stmt.setLong(1, product.getCompanyId());
            stmt.setString(2, product.getName());
            stmt.setString(3, product.getDescription() != null ? product.getDescription() : "");
            stmt.setString(4, product.getHsCode());

            if (categoryId != null)
                stmt.setLong(5, categoryId);
            else
                stmt.setNull(5, Types.BIGINT);
            stmt.setDouble(6, product.getQuantity() != null ? product.getQuantity() : 0);
            stmt.setString(7, product.getUnit() != null ? product.getUnit() : "");
            stmt.setDouble(8, product.getUnitPrice() != null ? product.getUnitPrice() : 0);
            stmt.setString(9, product.getCurrency() != null ? product.getCurrency() : "TND");
            stmt.setString(10, product.getOriginCriteria() != null ? product.getOriginCriteria() : "");

            // DEBUG: print values used for insert BEFORE executing
            System.out.println("[DEBUG] Executing INSERT SQL=" + sql + " params=[1=" + product.getCompanyId() + ",2='"
                    + product.getName() + "',3='" + (product.getDescription() != null ? product.getDescription() : "")
                    + "',4='" + product.getHsCode() + "',5='" + categoryId + "',6='"
                    + (product.getQuantity() != null ? product.getQuantity() : 0) + "',7='"
                    + (product.getUnit() != null ? product.getUnit() : "") + "',8='"
                    + (product.getUnitPrice() != null ? product.getUnitPrice() : 0) + "',9='"
                    + (product.getCurrency() != null ? product.getCurrency() : "TND") + "',10='"
                    + (product.getOriginCriteria() != null ? product.getOriginCriteria() : "") + "']");

            try {
                stmt.executeUpdate();
            } catch (SQLException ex) {
                System.out.println("[ERROR] INSERT failed, printing stacktrace and values");
                ex.printStackTrace();
                throw ex;
            }

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    product.setId(rs.getLong(1));
                }
            }
        }

        return product;
    }

    @Override
    public Product findById(Long id) throws SQLException {
        String sql = "SELECT * FROM products WHERE id = ?";

        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    @Override
    public List<Product> findAll() throws SQLException {
        String sql = "SELECT * FROM products ORDER BY name";
        List<Product> products = new ArrayList<>();

        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                products.add(mapResultSetToEntity(rs));
            }
        }

        return products;
    }

    @Override
    public boolean update(Product product) throws SQLException {
        System.out.println("[DEBUG] ProductDAO.update called for id=" + product.getId() + " product=" + product);
        String sql = "UPDATE products SET company_id = ?, name = ?, description = ?, " +
                "hs_code = ?, category_id = ?, quantity = ?, unit = ?, " +
                "unit_price = ?, currency = ?, origin_criteria = ?, last_updated = NOW() WHERE id = ?";

        ProductCategoryDAO pcDao = new ProductCategoryDAO();

        // ensure category exists and get id BEFORE preparing statement to avoid
        // reconnects
        Long categoryId = null;
        if (product.getCategory() != null) {
            if (product.getCategory().getId() != null)
                categoryId = product.getCategory().getId();
            else {
                ProductCategory found = pcDao.findByName(product.getCategory().getName());
                if (found != null)
                    categoryId = found.getId();
                else {
                    ProductCategory created = pcDao.create(product.getCategory());
                    categoryId = created.getId();
                    product.getCategory().setId(categoryId);
                }
            }
        }

        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {

            System.out.println("[DEBUG] Prepared statement created for UPDATE; conn=" + conn);

            stmt.setLong(1, product.getCompanyId());
            stmt.setString(2, product.getName());
            stmt.setString(3, product.getDescription() != null ? product.getDescription() : "");
            stmt.setString(4, product.getHsCode());
            if (categoryId != null)
                stmt.setLong(5, categoryId);
            else
                stmt.setNull(5, Types.BIGINT);
            stmt.setDouble(6, product.getQuantity() != null ? product.getQuantity() : 0);
            stmt.setString(7, product.getUnit() != null ? product.getUnit() : "");
            stmt.setDouble(8, product.getUnitPrice() != null ? product.getUnitPrice() : 0);
            stmt.setString(9, product.getCurrency() != null ? product.getCurrency() : "TND");
            stmt.setString(10, product.getOriginCriteria() != null ? product.getOriginCriteria() : "");
            stmt.setLong(11, product.getId());

            System.out.println("[DEBUG] Executing UPDATE SQL=" + sql + " params=[1=" + product.getCompanyId() + ",2='"
                    + product.getName() + "',3='" + (product.getDescription() != null ? product.getDescription() : "")
                    + "',4='" + product.getHsCode() + "',5='" + categoryId + "',6='"
                    + (product.getQuantity() != null ? product.getQuantity() : 0) + "',7='"
                    + (product.getUnit() != null ? product.getUnit() : "") + "',8='"
                    + (product.getUnitPrice() != null ? product.getUnitPrice() : 0) + "',9='"
                    + (product.getCurrency() != null ? product.getCurrency() : "TND") + "',10='"
                    + (product.getOriginCriteria() != null ? product.getOriginCriteria() : "") + "',11='"
                    + product.getId() + "']");

            boolean updated;
            try {
                updated = stmt.executeUpdate() > 0;
            } catch (SQLException ex) {
                System.out.println("[ERROR] UPDATE failed, printing stacktrace and values");
                ex.printStackTrace();
                throw ex;
            }

            System.out.println("[DEBUG] Updating product id=" + product.getId() + " => updated=" + updated);

            return updated;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM products WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM products WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1) > 0;
                }
            }
        }

        return false;
    }

    @Override
    public long count() throws SQLException {
        String sql = "SELECT COUNT(*) FROM products";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    public List<Product> findByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM products WHERE company_id = ? ORDER BY name";
        List<Product> products = new ArrayList<>();

        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    products.add(mapResultSetToEntity(rs));
                }
            }
        }

        return products;
    }

    public List<Product> findByCategory(ProductCategory category) throws SQLException {
        String sql = "SELECT * FROM products WHERE category_id = ? ORDER BY name";
        List<Product> products = new ArrayList<>();

        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {

            Long catId = category.getId();
            if (catId == null) {
                ProductCategoryDAO pcDao = new ProductCategoryDAO();
                ProductCategory found = pcDao.findByName(category.getName());
                if (found != null)
                    catId = found.getId();
            }
            if (catId == null)
                return products;
            stmt.setLong(1, catId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    products.add(mapResultSetToEntity(rs));
                }
            }
        }

        return products;
    }

    private Product mapResultSetToEntity(ResultSet rs) throws SQLException {
        Product product = new Product();
        product.setId(rs.getLong("id"));
        product.setCompanyId(rs.getLong("company_id"));
        product.setName(rs.getString("name"));
        product.setDescription(rs.getString("description") != null ? rs.getString("description") : "");
        product.setHsCode(rs.getString("hs_code"));
        long catId = rs.getLong("category_id");
        if (!rs.wasNull() && catId > 0) {
            ProductCategoryDAO pcDao = new ProductCategoryDAO();
            ProductCategory pc = pcDao.findById(catId);
            product.setCategory(pc != null ? pc : ProductCategory.valueOf("OTHER"));
        } else {
            product.setCategory(ProductCategory.valueOf("OTHER"));
        }
        double qty = rs.getDouble("quantity");
        product.setQuantity(!rs.wasNull() ? qty : 0.0);
        product.setUnit(rs.getString("unit") != null ? rs.getString("unit") : "");
        double up = rs.getDouble("unit_price");
        product.setUnitPrice(!rs.wasNull() ? up : 0.0);
        product.setCurrency(rs.getString("currency") != null ? rs.getString("currency") : "TND");
        product.setOriginCriteria(rs.getString("origin_criteria") != null ? rs.getString("origin_criteria") : "");
        return product;
    }
}