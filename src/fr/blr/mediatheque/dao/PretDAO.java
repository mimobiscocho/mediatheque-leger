package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Pret;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Types;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des prêts (livres et matériels). */
public class PretDAO {

    private static final String SELECT_BASE =
        "SELECT p.*, CONCAT(a.prenom, ' ', a.nom) AS adherent_nom, "
        + "l.titre AS livre_titre, m.nom AS materiel_nom "
        + "FROM pret p "
        + "JOIN adherent a ON p.adherent_id = a.id "
        + "LEFT JOIN livre l    ON p.livre_id = l.id "
        + "LEFT JOIN materiel m ON p.materiel_id = m.id ";

    public List<Pret> findAll() throws SQLException {
        return query(SELECT_BASE + "ORDER BY p.date_pret DESC");
    }

    /** Prêts en retard (non rendus, échéance dépassée). */
    public List<Pret> findEnRetard() throws SQLException {
        return query(SELECT_BASE
            + "WHERE p.date_retour_effective IS NULL AND p.date_retour_prevue < CURDATE() "
            + "ORDER BY p.date_retour_prevue");
    }

    private List<Pret> query(String sql) throws SQLException {
        List<Pret> list = new ArrayList<>();
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public int countEnCours() throws SQLException {
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM pret WHERE date_retour_effective IS NULL")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    /**
     * Enregistre un nouveau prêt. La disponibilité est contrôlée par le
     * trigger trg_pret_before_insert : une SQLException (message du SIGNAL)
     * est levée si le produit est indisponible.
     */
    public void insert(Pret p) throws SQLException {
        String sql = "INSERT INTO pret (adherent_id, livre_id, materiel_id, date_pret, date_retour_prevue, statut) "
            + "VALUES (?,?,?,?,?, 'en_cours')";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setInt(1, p.getAdherentId());
            if (p.getLivreId() == null) ps.setNull(2, Types.INTEGER);
            else ps.setInt(2, p.getLivreId());
            if (p.getMaterielId() == null) ps.setNull(3, Types.INTEGER);
            else ps.setInt(3, p.getMaterielId());
            ps.setString(4, p.getDatePret());
            ps.setString(5, p.getDateRetourPrevue());
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) p.setId(keys.getInt(1));
            }
        }
    }

    /** Enregistre le retour d'un prêt (le trigger restaure le stock). */
    public void retour(int id) throws SQLException {
        String sql = "UPDATE pret SET date_retour_effective = CURDATE(), statut = 'rendu' "
            + "WHERE id = ? AND date_retour_effective IS NULL";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public void delete(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement("DELETE FROM pret WHERE id=?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    private Pret map(ResultSet rs) throws SQLException {
        Pret p = new Pret();
        p.setId(rs.getInt("id"));
        p.setAdherentId(rs.getInt("adherent_id"));
        int lid = rs.getInt("livre_id");
        p.setLivreId(rs.wasNull() ? null : lid);
        int mid = rs.getInt("materiel_id");
        p.setMaterielId(rs.wasNull() ? null : mid);
        p.setDatePret(rs.getString("date_pret"));
        p.setDateRetourPrevue(rs.getString("date_retour_prevue"));
        p.setDateRetourEffective(rs.getString("date_retour_effective"));
        p.setStatut(rs.getString("statut"));
        p.setAdherentNom(rs.getString("adherent_nom"));
        p.setLivreTitre(rs.getString("livre_titre"));
        p.setMaterielNom(rs.getString("materiel_nom"));
        return p;
    }
}
