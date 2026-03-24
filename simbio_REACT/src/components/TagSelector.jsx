import React, { useState, useEffect } from 'react';

const TagSelector = ({ allAvailableTags = [] }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [selectedTags, setSelectedTags] = useState([]);

  useEffect(() => {
    if (!searchTerm.trim()) {
      setSuggestions([]);
      return;
    }

    const filtered = allAvailableTags
      .filter(tag => 
        tag.toLowerCase().includes(searchTerm.toLowerCase()) && 
        !selectedTags.includes(tag)
      )
      .slice(0, 8);

    setSuggestions(filtered);
  }, [searchTerm, selectedTags, allAvailableTags]);

  const addTag = (tag) => {
    if (!allAvailableTags.includes(tag)) {
      alert('Esta etiqueta no existe');
      return;
    }
    if (selectedTags.includes(tag)) {
      alert('Esta etiqueta ya está añadida');
      return;
    }

    setSelectedTags([...selectedTags, tag]);
    setSearchTerm('');
    setSuggestions([]);
  };

  const removeTag = (tagToRemove) => {
    setSelectedTags(selectedTags.filter(tag => tag !== tagToRemove));
  };

  return (
    <div className="tag-wrapper">
      <div id="tags-list" className="tags-container">
        {selectedTags.map((tag) => (
          <div key={tag} className="tag-item">
            <span>{tag}</span>
            <button 
              type="button" 
              className="remove-tag-btn" 
              onClick={() => removeTag(tag)}
            >
              ×
            </button>
            <input type="hidden" name="tags[]" value={tag} />
          </div>
        ))}
      </div>

      <div className="tag-search-wrapper" style={{ position: 'relative' }}>
        <input
          type="text"
          id="tag-search"
          placeholder="Buscar etiquetas..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          autoComplete="off"
        />
        <button type="button" id="add-tag-btn" onClick={() => addTag(searchTerm)}>
          Añadir
        </button>

        {suggestions.length > 0 && (
          <div id="tag-suggestions" className="suggestions-list">
            {suggestions.map((tag) => (
              <div
                key={tag}
                className="suggestion-item"
                onClick={() => addTag(tag)}
              >
                {tag}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default TagSelector;