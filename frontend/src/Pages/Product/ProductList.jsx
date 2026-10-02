import { useEffect, useState } from "react";
import api from "../../services/api";
import { useNavigate } from "react-router-dom";
import "./ProductList.css";

export default function ProductList() {
  const [products, setProducts] = useState([]);
  const navigate = useNavigate();

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedProductId, setSelectedProductId] = useState(null);

  const [viewProduct, setViewProduct] = useState(null);
  const [isViewOpen, setIsViewOpen] = useState(false);

  const loadProducts = async () => {
    const res = await api.get("/products");
    setProducts(res.data);
  };

  useEffect(() => {
    const fetchProducts = async () => {
      await loadProducts();
    };

    fetchProducts();
  }, []);

  const openDeleteModal = (id) => {
    setSelectedProductId(id);
    setIsModalOpen(true);
  };

  const confirmDelete = async () => {
    if (!selectedProductId) return;

    try {
      await api.delete(`/products/${selectedProductId}`);
      setIsModalOpen(false);
      setSelectedProductId(null);
      await loadProducts();
    } catch (error) {
      console.error("Failed to delete product:", error);
      setIsModalOpen(false);
      setSelectedProductId(null);
    }
  };

  const openViewModal = (product) => {
    setViewProduct(product);
    setIsViewOpen(true);
  };

  const closeViewModal = () => {
    setIsViewOpen(false);
    setViewProduct(null);
  };

  return (
    <div className="product-container">
      {/* Header */}
      <div className="product-header">
        <h2 className="header-title">Product List</h2>

        <button onClick={() => navigate("/create")} className="btn-add">
          + Add Product
        </button>
      </div>

      {/* Table Card */}
      <div className="table-card">
        <table className="product-table">
          <thead className="table-head">
            <tr>
              <th className="table-th">ID</th>
              <th className="table-th">Image</th>
              <th className="table-th">Name</th>
              <th className="table-th">Description</th>
              <th className="table-th">Quantity</th>
              <th className="table-th">Price</th>
              <th className="table-th">Actions</th>
            </tr>
          </thead>

          <tbody className="table-body">
            {products.map((p) => (
              <tr
                key={p.id}
                className="table-row table-row-clickable"
                onClick={() => openViewModal(p)}
              >
                <td className="table-td">{p.id}</td>

                <td className="table-td">
                  {p.image_url ? (
                    <img
                      src={p.image_url}
                      alt={p.name}
                      className="product-thumb"
                    />
                  ) : (
                    <div className="product-thumb-placeholder">No Image</div>
                  )}
                </td>

                <td className="table-td font-medium">{p.name}</td>
                <td className="table-td">
                  <div className="table-description-clamp">{p.description}</div>
                </td>
                <td className="table-td">{p.quantity}</td>
                <td className="table-td price-text">$ {p.price}</td>

                <td className="table-td action-cell">
                  <button
                    onClick={(e) => {
                      e.stopPropagation();
                      openDeleteModal(p.id);
                    }}
                    className="btn-delete"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* VIEW PRODUCT DETAILS MODAL */}
      {isViewOpen && viewProduct && (
        <div className="modal-overlay" onClick={closeViewModal}>
          <div
            className="modal-card view-modal-card"
            onClick={(e) => e.stopPropagation()}
          >
            <button className="view-modal-close" onClick={closeViewModal}>
              &times;
            </button>

            {viewProduct.image_url ? (
              <img
                src={viewProduct.image_url}
                alt={viewProduct.name}
                className="view-modal-image"
              />
            ) : (
              <div className="view-modal-image-placeholder">No Image</div>
            )}

            <h3 className="modal-title">{viewProduct.name}</h3>

            <p className="view-modal-description">
              {viewProduct.description || "No description available."}
            </p>

            <div className="view-modal-meta">
              <div className="view-modal-meta-item">
                <span className="view-modal-meta-label">Price</span>
                <span className="view-modal-meta-value">
                  $ {viewProduct.price}
                </span>
              </div>
              <div className="view-modal-meta-item">
                <span className="view-modal-meta-label">Quantity</span>
                <span className="view-modal-meta-value">
                  {viewProduct.quantity}
                </span>
              </div>
            </div>

            <div className="modal-actions">
              <button
                type="button"
                onClick={closeViewModal}
                className="btn-modal-cancel"
              >
                Close
              </button>
              <button
                type="button"
                onClick={() => navigate(`/edit/${viewProduct.id}`)}
                className="btn-modal-confirm"
              >
                Edit
              </button>
            </div>
          </div>
        </div>
      )}

      {/* CUSTOM SELECTION MODAL POPUP */}
      {isModalOpen && (
        <div className="modal-overlay">
          <div className="modal-card">
            <h3 className="modal-title">Confirm Deletion</h3>
            <p className="modal-text">
              Are you sure you want to permanently delete this item? This action
              cannot be undone.
            </p>

            {/* Clear Options to choose from */}
            <div className="modal-actions">
              <button
                type="button"
                onClick={() => {
                  setIsModalOpen(false);
                  setSelectedProductId(null);
                }} // Option 1: CANCEL
                className="btn-modal-cancel"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={confirmDelete} // Option 2: DELETE
                className="btn-modal-confirm"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
