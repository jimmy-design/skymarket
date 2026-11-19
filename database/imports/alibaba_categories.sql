-- SkyMarket category draft inspired by Alibaba.com's public departments.
-- Department names are from https://www.alibaba.com/ (accessed 2026-11-19).
-- Categories and subcategories are illustrative, curated examples for
-- SkyMarket; they are not represented as Alibaba's complete official tree.
--
-- Inserts directly into the existing ec_product_categories table shown in
-- phpMyAdmin. Departments use parent_id=0; child rows use their live parent ID.
-- Names below level 0 are illustrative SkyMarket categories and subcategories.

START TRANSACTION;

-- Level 0: departments visible on Alibaba.com's public homepage.
INSERT INTO `ec_product_categories`
    (`name`, `parent_id`, `status`, `order`, `is_featured`, `created_at`, `updated_at`)
SELECT x.name, 0, 'published', 0, 0, NOW(), NOW()
FROM (
  SELECT 'Apparel & Accessories' AS name UNION ALL
  SELECT 'Consumer Electronics' UNION ALL
  SELECT 'Sports & Entertainment' UNION ALL
  SELECT 'Jewelry, Eyewear & Watches' UNION ALL
  SELECT 'Packaging & Printing' UNION ALL
  SELECT 'Home & Garden' UNION ALL
  SELECT 'Sportswear & Outdoor Apparel' UNION ALL
  SELECT 'Beauty' UNION ALL
  SELECT 'Shoes & Accessories' UNION ALL
  SELECT 'Luggage, Bags & Cases' UNION ALL
  SELECT 'Parents, Kids & Toys' UNION ALL
  SELECT 'Personal Care & Home Care' UNION ALL
  SELECT 'Health & Medical' UNION ALL
  SELECT 'Gifts & Crafts' UNION ALL
  SELECT 'Pet Supplies' UNION ALL
  SELECT 'School & Office Supplies' UNION ALL
  SELECT 'Industrial Machinery' UNION ALL
  SELECT 'Commercial Equipment & Machinery' UNION ALL
  SELECT 'Construction & Building Machinery' UNION ALL
  SELECT 'Construction & Real Estate' UNION ALL
  SELECT 'Furniture' UNION ALL
  SELECT 'Lights & Lighting' UNION ALL
  SELECT 'Home Appliances' UNION ALL
  SELECT 'Automotive Supplies & Tools' UNION ALL
  SELECT 'Vehicle Parts & Accessories' UNION ALL
  SELECT 'Tools & Hardware' UNION ALL
  SELECT 'Renewable Energy' UNION ALL
  SELECT 'Electrical Equipment & Supplies' UNION ALL
  SELECT 'Safety & Security' UNION ALL
  SELECT 'Material Handling' UNION ALL
  SELECT 'Testing Instrument & Equipment' UNION ALL
  SELECT 'Power Transmission' UNION ALL
  SELECT 'Electronic Components' UNION ALL
  SELECT 'Vehicles & Transportation' UNION ALL
  SELECT 'Agriculture, Food & Beverage' UNION ALL
  SELECT 'Raw Materials' UNION ALL
  SELECT 'Fabrication Services' UNION ALL
  SELECT 'Services'
) AS x
LEFT JOIN `ec_product_categories` existing
  ON existing.name=x.name AND existing.parent_id=0
WHERE existing.id IS NULL;

-- Level 1: practical SkyMarket browsing categories, grouped under departments.
INSERT INTO `ec_product_categories`
    (`name`, `parent_id`, `status`, `order`, `is_featured`, `created_at`, `updated_at`)
SELECT x.name, parent_category.id, 'published', 0, 0, NOW(), NOW()
FROM (
  SELECT 'Apparel & Accessories' AS department, 'Women''s Clothing' AS name UNION ALL
  SELECT 'Apparel & Accessories','Men''s Clothing' UNION ALL
  SELECT 'Apparel & Accessories','Children''s Clothing' UNION ALL
  SELECT 'Apparel & Accessories','Fabric & Textiles' UNION ALL
  SELECT 'Consumer Electronics','Mobile Phones & Accessories' UNION ALL
  SELECT 'Consumer Electronics','Computers & Accessories' UNION ALL
  SELECT 'Consumer Electronics','Audio & Video' UNION ALL
  SELECT 'Consumer Electronics','Cameras & Photography' UNION ALL
  SELECT 'Sports & Entertainment','Sports Equipment' UNION ALL
  SELECT 'Sports & Entertainment','Outdoor Recreation' UNION ALL
  SELECT 'Sports & Entertainment','Fitness & Exercise' UNION ALL
  SELECT 'Jewelry, Eyewear & Watches','Fashion Jewelry' UNION ALL
  SELECT 'Jewelry, Eyewear & Watches','Watches' UNION ALL
  SELECT 'Jewelry, Eyewear & Watches','Eyewear' UNION ALL
  SELECT 'Packaging & Printing','Packaging Materials' UNION ALL
  SELECT 'Packaging & Printing','Printing Supplies' UNION ALL
  SELECT 'Home & Garden','Home Decor' UNION ALL
  SELECT 'Home & Garden','Kitchen & Dining' UNION ALL
  SELECT 'Home & Garden','Garden Supplies' UNION ALL
  SELECT 'Beauty','Makeup & Cosmetics' UNION ALL
  SELECT 'Beauty','Skin Care' UNION ALL
  SELECT 'Beauty','Hair Care & Styling' UNION ALL
  SELECT 'Shoes & Accessories','Women''s Shoes' UNION ALL
  SELECT 'Shoes & Accessories','Men''s Shoes' UNION ALL
  SELECT 'Shoes & Accessories','Bags & Wallets' UNION ALL
  SELECT 'Luggage, Bags & Cases','Travel Luggage' UNION ALL
  SELECT 'Luggage, Bags & Cases','Backpacks & Handbags' UNION ALL
  SELECT 'Parents, Kids & Toys','Baby Care & Feeding' UNION ALL
  SELECT 'Parents, Kids & Toys','Baby Gear' UNION ALL
  SELECT 'Parents, Kids & Toys','Toys & Games' UNION ALL
  SELECT 'Personal Care & Home Care','Personal Hygiene' UNION ALL
  SELECT 'Personal Care & Home Care','Household Cleaning' UNION ALL
  SELECT 'Health & Medical','Medical Supplies' UNION ALL
  SELECT 'Health & Medical','Health Care Equipment' UNION ALL
  SELECT 'Gifts & Crafts','Gift Items' UNION ALL
  SELECT 'Gifts & Crafts','Arts & Crafts' UNION ALL
  SELECT 'Pet Supplies','Pet Food' UNION ALL
  SELECT 'Pet Supplies','Pet Accessories' UNION ALL
  SELECT 'School & Office Supplies','School Supplies' UNION ALL
  SELECT 'School & Office Supplies','Office Supplies' UNION ALL
  SELECT 'Industrial Machinery','Manufacturing Machinery' UNION ALL
  SELECT 'Industrial Machinery','Packaging Machinery' UNION ALL
  SELECT 'Commercial Equipment & Machinery','Restaurant Equipment' UNION ALL
  SELECT 'Commercial Equipment & Machinery','Retail Equipment' UNION ALL
  SELECT 'Construction & Building Machinery','Earthmoving Machinery' UNION ALL
  SELECT 'Construction & Building Machinery','Concrete & Masonry Equipment' UNION ALL
  SELECT 'Construction & Real Estate','Building Materials' UNION ALL
  SELECT 'Construction & Real Estate','Doors, Windows & Fixtures' UNION ALL
  SELECT 'Furniture','Living Room Furniture' UNION ALL
  SELECT 'Furniture','Bedroom Furniture' UNION ALL
  SELECT 'Furniture','Office Furniture' UNION ALL
  SELECT 'Lights & Lighting','Indoor Lighting' UNION ALL
  SELECT 'Lights & Lighting','Outdoor Lighting' UNION ALL
  SELECT 'Home Appliances','Kitchen Appliances' UNION ALL
  SELECT 'Home Appliances','Cleaning Appliances' UNION ALL
  SELECT 'Automotive Supplies & Tools','Car Care & Accessories' UNION ALL
  SELECT 'Automotive Supplies & Tools','Automotive Tools' UNION ALL
  SELECT 'Vehicle Parts & Accessories','Car Parts' UNION ALL
  SELECT 'Vehicle Parts & Accessories','Motorcycle Parts' UNION ALL
  SELECT 'Tools & Hardware','Hand Tools' UNION ALL
  SELECT 'Tools & Hardware','Power Tools' UNION ALL
  SELECT 'Renewable Energy','Solar Energy Products' UNION ALL
  SELECT 'Renewable Energy','Batteries & Storage' UNION ALL
  SELECT 'Electrical Equipment & Supplies','Electrical Components' UNION ALL
  SELECT 'Electrical Equipment & Supplies','Wires & Cables' UNION ALL
  SELECT 'Safety & Security','Personal Protective Equipment' UNION ALL
  SELECT 'Safety & Security','Security Systems' UNION ALL
  SELECT 'Material Handling','Lifting Equipment' UNION ALL
  SELECT 'Material Handling','Storage & Shelving' UNION ALL
  SELECT 'Testing Instrument & Equipment','Measurement Instruments' UNION ALL
  SELECT 'Testing Instrument & Equipment','Laboratory Equipment' UNION ALL
  SELECT 'Power Transmission','Bearings & Components' UNION ALL
  SELECT 'Power Transmission','Motors & Drives' UNION ALL
  SELECT 'Electronic Components','Semiconductors' UNION ALL
  SELECT 'Electronic Components','Connectors & Passive Components' UNION ALL
  SELECT 'Vehicles & Transportation','Electric Vehicles' UNION ALL
  SELECT 'Vehicles & Transportation','Bicycles & Scooters' UNION ALL
  SELECT 'Agriculture, Food & Beverage','Agriculture Products' UNION ALL
  SELECT 'Agriculture, Food & Beverage','Food & Beverage' UNION ALL
  SELECT 'Raw Materials','Metals & Alloys' UNION ALL
  SELECT 'Raw Materials','Plastics & Polymers' UNION ALL
  SELECT 'Fabrication Services','Metal Fabrication' UNION ALL
  SELECT 'Fabrication Services','CNC & Machining Services' UNION ALL
  SELECT 'Services','Business Services' UNION ALL
  SELECT 'Services','Logistics & Sourcing Services'
) AS x
JOIN `ec_product_categories` parent_category
  ON parent_category.name=x.department AND parent_category.parent_id=0
LEFT JOIN `ec_product_categories` existing
  ON existing.name=x.name AND existing.parent_id=parent_category.id
WHERE existing.id IS NULL;

-- Level 2: illustrative subcategories for common retail categories.
INSERT INTO `ec_product_categories`
    (`name`, `parent_id`, `status`, `order`, `is_featured`, `created_at`, `updated_at`)
SELECT x.name, parent_category.id, 'published', 0, 0, NOW(), NOW()
FROM (
  SELECT 'Women''s Clothing' AS category, 'Dresses' AS name UNION ALL
  SELECT 'Women''s Clothing','Tops & Blouses' UNION ALL
  SELECT 'Men''s Clothing','Shirts' UNION ALL
  SELECT 'Men''s Clothing','Trousers & Jeans' UNION ALL
  SELECT 'Children''s Clothing','Baby Clothing' UNION ALL
  SELECT 'Children''s Clothing','Kids'' Sets & Outfits' UNION ALL
  SELECT 'Fabric & Textiles','Cotton Fabric' UNION ALL
  SELECT 'Fabric & Textiles','Textile Accessories' UNION ALL
  SELECT 'Mobile Phones & Accessories','Smartphones' UNION ALL
  SELECT 'Mobile Phones & Accessories','Phone Cases & Screen Protectors' UNION ALL
  SELECT 'Computers & Accessories','Laptops' UNION ALL
  SELECT 'Computers & Accessories','Keyboards & Mice' UNION ALL
  SELECT 'Audio & Video','Headphones & Earphones' UNION ALL
  SELECT 'Audio & Video','Speakers' UNION ALL
  SELECT 'Cameras & Photography','Digital Cameras' UNION ALL
  SELECT 'Cameras & Photography','Camera Lenses' UNION ALL
  SELECT 'Sports Equipment','Bicycles' UNION ALL
  SELECT 'Sports Equipment','Team Sports Equipment' UNION ALL
  SELECT 'Outdoor Recreation','Camping Tents' UNION ALL
  SELECT 'Outdoor Recreation','Camping Furniture' UNION ALL
  SELECT 'Fitness & Exercise','Exercise Machines' UNION ALL
  SELECT 'Fitness & Exercise','Yoga & Training Accessories' UNION ALL
  SELECT 'Fashion Jewelry','Necklaces & Pendants' UNION ALL
  SELECT 'Fashion Jewelry','Earrings' UNION ALL
  SELECT 'Watches','Quartz Watches' UNION ALL
  SELECT 'Watches','Smart Watches' UNION ALL
  SELECT 'Eyewear','Sunglasses' UNION ALL
  SELECT 'Eyewear','Optical Frames' UNION ALL
  SELECT 'Packaging Materials','Paper Packaging' UNION ALL
  SELECT 'Packaging Materials','Plastic Packaging' UNION ALL
  SELECT 'Printing Supplies','Printing Ink' UNION ALL
  SELECT 'Printing Supplies','Labels & Stickers' UNION ALL
  SELECT 'Home Decor','Wall Decor' UNION ALL
  SELECT 'Home Decor','Decorative Accessories' UNION ALL
  SELECT 'Kitchen & Dining','Cookware' UNION ALL
  SELECT 'Kitchen & Dining','Tableware' UNION ALL
  SELECT 'Garden Supplies','Garden Tools' UNION ALL
  SELECT 'Garden Supplies','Planters & Pots' UNION ALL
  SELECT 'Makeup & Cosmetics','Lip Makeup' UNION ALL
  SELECT 'Makeup & Cosmetics','Nail Products' UNION ALL
  SELECT 'Skin Care','Face Creams' UNION ALL
  SELECT 'Skin Care','Cleansers & Toners' UNION ALL
  SELECT 'Hair Care & Styling','Hair Dryers' UNION ALL
  SELECT 'Hair Care & Styling','Wigs & Extensions' UNION ALL
  SELECT 'Women''s Shoes','Sneakers' UNION ALL
  SELECT 'Women''s Shoes','Sandals' UNION ALL
  SELECT 'Men''s Shoes','Casual Shoes' UNION ALL
  SELECT 'Men''s Shoes','Work & Safety Shoes' UNION ALL
  SELECT 'Bags & Wallets','Handbags' UNION ALL
  SELECT 'Bags & Wallets','Wallets' UNION ALL
  SELECT 'Travel Luggage','Suitcases' UNION ALL
  SELECT 'Travel Luggage','Travel Bags' UNION ALL
  SELECT 'Backpacks & Handbags','Backpacks' UNION ALL
  SELECT 'Backpacks & Handbags','Shoulder Bags' UNION ALL
  SELECT 'Baby Care & Feeding','Baby Bottles' UNION ALL
  SELECT 'Baby Care & Feeding','Diapers & Changing' UNION ALL
  SELECT 'Baby Gear','Baby Strollers' UNION ALL
  SELECT 'Baby Gear','Car Seats' UNION ALL
  SELECT 'Toys & Games','Educational Toys' UNION ALL
  SELECT 'Toys & Games','Dolls & Plush Toys' UNION ALL
  SELECT 'Personal Hygiene','Oral Care' UNION ALL
  SELECT 'Personal Hygiene','Bath & Body' UNION ALL
  SELECT 'Household Cleaning','Cleaning Tools' UNION ALL
  SELECT 'Household Cleaning','Laundry Products' UNION ALL
  SELECT 'Medical Supplies','Masks & Protective Supplies' UNION ALL
  SELECT 'Medical Supplies','First Aid Supplies' UNION ALL
  SELECT 'Health Care Equipment','Blood Pressure Monitors' UNION ALL
  SELECT 'Health Care Equipment','Thermometers' UNION ALL
  SELECT 'Gift Items','Gift Boxes & Bags' UNION ALL
  SELECT 'Gift Items','Personalized Gifts' UNION ALL
  SELECT 'Arts & Crafts','Craft Supplies' UNION ALL
  SELECT 'Arts & Crafts','DIY Kits' UNION ALL
  SELECT 'Pet Food','Dog Food' UNION ALL
  SELECT 'Pet Food','Cat Food' UNION ALL
  SELECT 'Pet Accessories','Pet Beds' UNION ALL
  SELECT 'Pet Accessories','Collars & Leashes' UNION ALL
  SELECT 'School Supplies','Writing Supplies' UNION ALL
  SELECT 'School Supplies','Backpacks & School Bags' UNION ALL
  SELECT 'Office Supplies','Paper Products' UNION ALL
  SELECT 'Office Supplies','Desk Organizers' UNION ALL
  SELECT 'Manufacturing Machinery','Textile Machinery' UNION ALL
  SELECT 'Manufacturing Machinery','Plastic Processing Machinery' UNION ALL
  SELECT 'Packaging Machinery','Filling Machines' UNION ALL
  SELECT 'Packaging Machinery','Sealing Machines' UNION ALL
  SELECT 'Restaurant Equipment','Commercial Ovens' UNION ALL
  SELECT 'Restaurant Equipment','Refrigeration Equipment' UNION ALL
  SELECT 'Retail Equipment','Display Shelves' UNION ALL
  SELECT 'Retail Equipment','Point of Sale Equipment' UNION ALL
  SELECT 'Earthmoving Machinery','Excavators' UNION ALL
  SELECT 'Earthmoving Machinery','Loaders' UNION ALL
  SELECT 'Concrete & Masonry Equipment','Concrete Mixers' UNION ALL
  SELECT 'Concrete & Masonry Equipment','Concrete Pumps' UNION ALL
  SELECT 'Building Materials','Flooring' UNION ALL
  SELECT 'Building Materials','Roofing Materials' UNION ALL
  SELECT 'Doors, Windows & Fixtures','Doors' UNION ALL
  SELECT 'Doors, Windows & Fixtures','Windows' UNION ALL
  SELECT 'Living Room Furniture','Sofas' UNION ALL
  SELECT 'Living Room Furniture','Coffee Tables' UNION ALL
  SELECT 'Bedroom Furniture','Beds & Bed Frames' UNION ALL
  SELECT 'Bedroom Furniture','Wardrobes' UNION ALL
  SELECT 'Office Furniture','Office Chairs' UNION ALL
  SELECT 'Office Furniture','Office Desks' UNION ALL
  SELECT 'Indoor Lighting','Ceiling Lights' UNION ALL
  SELECT 'Indoor Lighting','Table & Floor Lamps' UNION ALL
  SELECT 'Outdoor Lighting','Solar Street Lights' UNION ALL
  SELECT 'Outdoor Lighting','Garden Lights' UNION ALL
  SELECT 'Kitchen Appliances','Blenders' UNION ALL
  SELECT 'Kitchen Appliances','Coffee Machines' UNION ALL
  SELECT 'Cleaning Appliances','Vacuum Cleaners' UNION ALL
  SELECT 'Cleaning Appliances','Floor Cleaners' UNION ALL
  SELECT 'Car Care & Accessories','Car Covers' UNION ALL
  SELECT 'Car Care & Accessories','Car Electronics' UNION ALL
  SELECT 'Automotive Tools','Diagnostic Tools' UNION ALL
  SELECT 'Automotive Tools','Repair Tools' UNION ALL
  SELECT 'Car Parts','Brake Parts' UNION ALL
  SELECT 'Car Parts','Engine Parts' UNION ALL
  SELECT 'Motorcycle Parts','Motorcycle Helmets' UNION ALL
  SELECT 'Motorcycle Parts','Motorcycle Tires' UNION ALL
  SELECT 'Hand Tools','Wrenches & Sockets' UNION ALL
  SELECT 'Hand Tools','Screwdrivers' UNION ALL
  SELECT 'Power Tools','Drills' UNION ALL
  SELECT 'Power Tools','Grinders & Sanders' UNION ALL
  SELECT 'Solar Energy Products','Solar Panels' UNION ALL
  SELECT 'Solar Energy Products','Solar Inverters' UNION ALL
  SELECT 'Batteries & Storage','Lithium Batteries' UNION ALL
  SELECT 'Batteries & Storage','Battery Chargers' UNION ALL
  SELECT 'Electrical Components','Switches & Sockets' UNION ALL
  SELECT 'Electrical Components','Circuit Breakers' UNION ALL
  SELECT 'Wires & Cables','Power Cables' UNION ALL
  SELECT 'Wires & Cables','Communication Cables' UNION ALL
  SELECT 'Personal Protective Equipment','Safety Gloves' UNION ALL
  SELECT 'Personal Protective Equipment','Safety Helmets' UNION ALL
  SELECT 'Security Systems','Security Cameras' UNION ALL
  SELECT 'Security Systems','Access Control Systems' UNION ALL
  SELECT 'Lifting Equipment','Hoists' UNION ALL
  SELECT 'Lifting Equipment','Forklifts' UNION ALL
  SELECT 'Storage & Shelving','Warehouse Racks' UNION ALL
  SELECT 'Storage & Shelving','Storage Bins' UNION ALL
  SELECT 'Measurement Instruments','Digital Multimeters' UNION ALL
  SELECT 'Measurement Instruments','Scales & Balances' UNION ALL
  SELECT 'Laboratory Equipment','Lab Glassware' UNION ALL
  SELECT 'Laboratory Equipment','Lab Testing Instruments' UNION ALL
  SELECT 'Bearings & Components','Ball Bearings' UNION ALL
  SELECT 'Bearings & Components','Linear Bearings' UNION ALL
  SELECT 'Motors & Drives','Electric Motors' UNION ALL
  SELECT 'Motors & Drives','Frequency Inverters' UNION ALL
  SELECT 'Semiconductors','Integrated Circuits' UNION ALL
  SELECT 'Semiconductors','LED Chips' UNION ALL
  SELECT 'Connectors & Passive Components','Electronic Connectors' UNION ALL
  SELECT 'Connectors & Passive Components','Resistors & Capacitors' UNION ALL
  SELECT 'Electric Vehicles','Electric Cars' UNION ALL
  SELECT 'Electric Vehicles','Electric Motorcycles' UNION ALL
  SELECT 'Bicycles & Scooters','Bicycles' UNION ALL
  SELECT 'Bicycles & Scooters','Electric Scooters' UNION ALL
  SELECT 'Agriculture Products','Seeds' UNION ALL
  SELECT 'Agriculture Products','Farm Machinery' UNION ALL
  SELECT 'Food & Beverage','Coffee & Tea' UNION ALL
  SELECT 'Food & Beverage','Packaged Foods' UNION ALL
  SELECT 'Metals & Alloys','Aluminum' UNION ALL
  SELECT 'Metals & Alloys','Steel' UNION ALL
  SELECT 'Plastics & Polymers','Plastic Resin' UNION ALL
  SELECT 'Plastics & Polymers','Rubber Materials' UNION ALL
  SELECT 'Metal Fabrication','Welding Services' UNION ALL
  SELECT 'Metal Fabrication','Sheet Metal Work' UNION ALL
  SELECT 'CNC & Machining Services','CNC Milling' UNION ALL
  SELECT 'CNC & Machining Services','CNC Turning' UNION ALL
  SELECT 'Business Services','Product Design Services' UNION ALL
  SELECT 'Business Services','Inspection Services' UNION ALL
  SELECT 'Logistics & Sourcing Services','Freight Forwarding' UNION ALL
  SELECT 'Logistics & Sourcing Services','Sourcing Services'
) AS x
JOIN `ec_product_categories` parent_category
  ON parent_category.name=x.category AND parent_category.parent_id<>0
LEFT JOIN `ec_product_categories` existing
  ON existing.name=x.name AND existing.parent_id=parent_category.id
WHERE existing.id IS NULL;

COMMIT;

-- Browse the hierarchy:
-- SELECT d.name AS department, c.name AS category, s.name AS subcategory
-- FROM `ec_product_categories` d
-- LEFT JOIN `ec_product_categories` c ON c.parent_id=d.id
-- LEFT JOIN `ec_product_categories` s ON s.parent_id=c.id
-- WHERE d.parent_id=0 ORDER BY d.name, c.name, s.name;
