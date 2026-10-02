import { useState } from "react";
import api from "../../services/api";
import { useNavigate } from "react-router-dom";
import "./CreateProduct.css";

export default function CreateProduct() {
  const [form, setForm] = useState({
    name: "",
    price: "",
    description: "",
    quantity: "",
  });

  // State to hold the actual file and its local preview URL
  const [image, setImage] = useState(null);
  const [preview, setPreview] = useState("");

  const navigate = useNavigate();

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleIntegerChange = (e) => {
    const { name, value } = e.target;
    if (/^\d*$/.test(value)) {
      setForm({ ...form, [name]: value });
    }
  };

  // Handle image input selection
  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setImage(file);
      setPreview(URL.createObjectURL(file)); // Generates a temporary local preview URL
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Create a FormData object to handle binary file uploads
    const formData = new FormData();
    formData.append("name", form.name);
    formData.append("price", form.price);
    formData.append("description", form.description || "");
    formData.append("quantity", form.quantity);

    if (image instanceof File) {
      formData.append("image", image);
    }

    try {
      await api.post("/products", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      navigate("/products");
    } catch (error) {
      if (error.response && error.response.data) {
        console.error("SERVER CRASH DETAILS:", error.response.data);
      } else {
        console.error("Error creating product:", error);
      }
    }
  };

  return (
    <div className="form-page-container">
      <div className="form-card">
        <h2 className="form-title">Create Product</h2>

        <form onSubmit={handleSubmit} className="product-form">
          {/* Name */}
          <div className="form-group">
            <label className="form-label">Name</label>
            <input
              name="name"
              value={form.name}
              placeholder="Enter product name"
              onChange={handleChange}
              className="form-input"
              required
            />
          </div>

          {/* Price */}
          <div className="form-group">
            <label className="form-label">Price</label>
            <input
              type="number"
              step="0.01"
              min="0"
              name="price"
              value={form.price}
              placeholder="0.00"
              onChange={handleChange}
              className="form-input"
              required
            />
          </div>

          {/* Description */}
          <div className="form-group">
            <label className="form-label">Description</label>
            <textarea
              name="description"
              value={form.description}
              placeholder="Enter description"
              onChange={handleChange}
              className="form-textarea"
            />
          </div>

          {/* Quantity */}
          <div className="form-group">
            <label className="form-label">Quantity</label>
            <input
              type="text"
              inputMode="numeric"
              name="quantity"
              value={form.quantity}
              placeholder="0"
              onChange={handleIntegerChange}
              className="form-input"
              required
            />
          </div>

          {/* Product Image Input */}
          <div className="form-group">
            <label className="form-label">Product Image</label>
            <input
              type="file"
              accept="image/*"
              onChange={handleImageChange}
              className="form-input"
            />
            {preview && (
              <div className="image-preview" style={{ marginTop: "10px" }}>
                <img
                  src={preview}
                  alt="Preview"
                  style={{
                    maxWidth: "150px",
                    borderRadius: "8px",
                    border: "1px solid #ccc",
                  }}
                />
              </div>
            )}
          </div>

          {/* Buttons */}
          <div className="form-actions">
            <button type="submit" className="btn-submit">
              Save Product
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
