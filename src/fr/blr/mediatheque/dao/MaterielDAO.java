package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Materiel;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des matériels (CRUD via requêtes préparées). */
public class MaterielDAO {

    public List<Materiel> findAll() throws SQLException {
        return query("SELECT * FROM materiel ORDER BY nom");
    }

    /** Matériels disponibles à l'emprunt. */
    public List<Materiel> findDisponibles() throws SQLException {
        return query("SELECT * FROM materiel WHERE disponible = 1 AND etat <> 'hors_service' ORDER BY nom");
    }

    private List<Materiel> query(String sql) throws SQLException {
        List<Materiel> list = new ArrayList<>();
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public void insert(Materiel m) throws SQLException {
        String sql = "INSERT INTO materiel (nom, categorie, description, etat, disponible) VALUES (?,?,?,?,?)";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            bind(ps, m);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) m.setId(keys.getInt(1));
            }
        }
    }

    public void update(Materiel m) throws SQLException {
        String sql = "UPDATE materiel SET nom=?, categorie=?, description=?, etat=?, disponible=? WHERE id=?";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql)) {
            bind(ps, m);
            ps.setInt(6, m.getId());
            ps.executeUpdate();
        }
    }

    public void delete(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement("DELETE FROM materiel WHERE id=?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public int count() throws SQLException {
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM materiel")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    private void bind(PreparedStatement ps, Materiel m) throws SQLException {
        ps.setString(1, m.getNom());
        ps.setString(2, m.getCategorie());
        ps.setString(3, m.getDescription());
        ps.setString(4, m.getEtat());
        ps.setBoolean(5, m.isDisponible());
    }

    private Materiel map(ResultSet rs) throws SQLException {
        Materiel m = new Materiel();
        m.setId(rs.getInt("id"));
        m.setNom(rs.getString("nom"));
        m.setCategorie(rs.getString("categorie"));
        m.setDescription(rs.getString("description"));
        m.setEtat(rs.getString("etat"));
        m.setDisponible(rs.getBoolean("disponible"));
        return m;
    }
}
