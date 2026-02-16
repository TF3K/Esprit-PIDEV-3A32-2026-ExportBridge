

package services;

import Utils.DBConnection;
import entites.Produit;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProduitCRUD {

    private final Connection cnx;

    public ProduitCRUD() {
        cnx = DBConnection.getConnection(); // ✅ correction
    }

    public List<Produit> getAll() {
        List<Produit> list = new ArrayList<>();

        String sql = "SELECT p.id, p.name, p.price, p.stock, p.category_id, " +
                "c.name AS categoryName " +
                "FROM produit p " +
                "LEFT JOIN category c ON p.category_id = c.id";

        try (Statement st = cnx.createStatement();
             ResultSet rs = st.executeQuery(sql)) {

            while (rs.next()) {
                Produit p = new Produit();
                p.setId(rs.getInt("id"));
                p.setName(rs.getString("name"));
                p.setPrice(rs.getDouble("price"));
                p.setQuantity(rs.getInt("stock"));
                p.setCategoryId(rs.getInt("category_id"));
                p.setCategoryName(rs.getString("categoryName"));
                list.add(p);
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return list;
    }

    public void add(Produit p) {
        String sql = "INSERT INTO produit(name, price, stock, category_id) VALUES(?,?,?,?)";

        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, p.getName());
            ps.setDouble(2, p.getPrice());
            ps.setInt(3, p.getQuantity());
            ps.setInt(4, p.getCategoryId());
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void update(Produit p) {
        String sql = "UPDATE produit SET name=?, price=?, stock=?, category_id=? WHERE id=?";

        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, p.getName());
            ps.setDouble(2, p.getPrice());
            ps.setInt(3, p.getQuantity());
            ps.setInt(4, p.getCategoryId());
            ps.setInt(5, p.getId());
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void delete(int id) {
        String sql = "DELETE FROM produit WHERE id=?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
