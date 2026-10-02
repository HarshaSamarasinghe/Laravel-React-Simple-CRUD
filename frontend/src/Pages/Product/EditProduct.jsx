import { useEffect, useState } from "react";
import api from "../../services/api";
import { useNavigate, useParams } from "react-router-dom";
import "./EditProduct.css";

export default function EditProduct() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: "",
    price: "",
    description: "",
    quantity: "",
  });

  const [imageFile, setImageFile] = useState(null);
  const [currentImageUrl, setCurrentImageUrl] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const loadProduct = async () => {
      const res = await api.get(`/products/${id}`);
      setForm(res.data);
      setCurrentImageUrl(res.data.image_url || null);
    };

    loadProduct();
  }, [id]);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleImageChange = (e) => {
    setImageFile(e.target.files[0]);
  };

  const handleUpdate = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const formData = new FormData();
      formData.append("name", form.name || "");
      formData.append("price", form.price || "");
      formData.append("description", form.description || "");
      formData.append("quantity", form.quantity || "");

      if (imageFile) {
        formData.append("image", imageFile);
      }

      // Laravel needs this to treat a POST as PUT when sending multipart/form-data
      formData.append("_method", "PUT");

      await api.post(`/products/${id}`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      navigate("/products");
    } catch (error) {
      console.error("Failed to update product:", error);
      alert(
        error.response?.data?.error_message ||
          "Failed to update product. Please try again.",
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="form-page-container">
      <div className="form-card">
        <h2 className="form-title">Edit Product</h2>

        <form onSubmit={handleUpdate} className="product-form">
          {/* Name */}
          <div className="form-group">
            <label className="form-label">Name</label>
            <input
              name="name"
              value={form.name || ""}
              onChange={handleChange}
              placeholder="Product Name"
              className="form-input"
            />
          </div>

          {/* Description */}
          <div className="form-group">
            <label className="form-label">Description</label>
            <textarea
              name="description"
              value={form.description || ""}
              onChange={handleChange}
              placeholder="Product Description"
              className="form-textarea"
            />
          </div>

          {/* Price */}
          <div className="form-group">
            <label className="form-label">Price</label>
            <input
              name="price"
              value={form.price || ""}
              onChange={handleChange}
              placeholder="Price"
              className="form-input"
            />
          </div>

          {/* Quantity */}
          <div className="form-group">
            <label className="form-label">Quantity</label>
            <input
              name="quantity"
              value={form.quantity || ""}
              onChange={handleChange}
              placeholder="Quantity"
              className="form-input"
            />
          </div>

          {/* Image */}
          <div className="form-group">
            <label className="form-label">Product Image</label>

            {currentImageUrl && !imageFile && (
              <div className="current-image-preview">
                <img
                  src={currentImageUrl}
                  alt="Current product"
                  className="preview-img"
                />
                <span className="preview-label">Current image</span>
              </div>
            )}

            {imageFile && (
              <div className="current-image-preview">
                <img
                  src={URL.createObjectURL(imageFile)}
                  alt="New preview"
                  className="preview-img"
                />
                <span className="preview-label">New image (not saved yet)</span>
              </div>
            )}

            <input
              type="file"
              name="image"
              accept="image/*"
              onChange={handleImageChange}
              className="form-input"
            />
          </div>

          {/* Buttons */}
          <div className="form-actions">
            <button type="submit" className="btn-update" disabled={loading}>
              {loading ? "Updating..." : "Update Product"}
            </button>

            <button
              type="button"
              onClick={() => navigate("/")}
              className="btn-cancel"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
