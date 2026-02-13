<?php

return [
    'menu' => 'Combo',

    'yes' => 'Có',
    'no' => 'Không',

    'metabox' => [
        'title' => 'Cấu hình combo',
        'is_bundle' => 'Sản phẩm này là combo',
    ],

    'list' => [
        'title' => 'Danh sách combo',
        'create' => 'Tạo combo',
        'empty' => 'Chưa có combo nào.',
        'columns' => [
            'id' => '#',
            'name' => 'Tên',
            'type' => 'Loại',
            'pricing' => 'Giá/Khuyến mãi',
            'active' => 'Kích hoạt',
            'actions' => 'Thao tác',
        ],
        'confirm_delete' => 'Xóa combo này?',
        'buttons' => [
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
    ],

    'form' => [
        'create_title' => 'Tạo combo',
        'edit_title' => 'Sửa combo',
        'fields' => [
            'name' => 'Tên combo',
            'slug' => 'Đường dẫn (slug)',
            'description' => 'Mô tả',
            'image' => 'Ảnh combo',
            'type' => 'Loại',
            'pricing_rule' => 'Quy tắc giá',
            'pricing_value' => 'Giá trị',
            'start_date' => 'Bắt đầu',
            'end_date' => 'Kết thúc',
            'active' => 'Kích hoạt',
            'featured' => 'Nổi bật',
            'attach_products' => 'Gắn vào sản phẩm',
            'attach_help' => 'Đây là các trang chi tiết sản phẩm sẽ hiển thị combo.',

            'slug_help' => 'Để trống sẽ tự sinh theo tên. Dùng cho đường dẫn trang chi tiết combo.',
            'image_help' => 'Chấp nhận đường dẫn media hoặc URL đầy đủ. Dùng khi hiển thị combo dạng thẻ.',

            'group_name' => 'Tên nhóm',
            'min' => 'Min',
            'max' => 'Max',
        ],
        'types' => [
            'fixed' => 'Combo cố định',
            'mix' => 'Mix & match',
        ],
        'pricing_types' => [
            'fixed_total' => 'Tổng giá cố định',
            'percent_off' => 'Giảm theo %',
            'amount_off' => 'Giảm theo số tiền',
        ],
        'sections' => [
            'fixed_items' => 'Sản phẩm cố định',
            'mix_groups' => 'Nhóm lựa chọn',
            'group' => 'Nhóm',
            'items' => 'Sản phẩm',
            'tips' => 'Gợi ý',
        ],
        'table' => [
            'product' => 'Sản phẩm',
            'variation_id' => 'ID biến thể',
            'qty' => 'SL',
        ],
        'actions' => [
            'save' => 'Lưu',
            'back' => 'Quay lại',
            'add_item' => 'Thêm sản phẩm',
            'remove' => 'Xóa',
            'add_group' => 'Thêm nhóm',
            'remove_group' => 'Xóa nhóm',
        ],
        'placeholders' => [
            'search_product' => 'Tìm sản phẩm...',
            'select_products' => 'Chọn sản phẩm...',
            'slug' => 'tự sinh',
            'image' => 'vd: /storage/bundles/combo.jpg',
        ],
        'labels' => [
            'variation' => 'Biến thể',
        ],
        'empties' => [
            'no_fixed_items' => 'Chưa có sản phẩm nào.',
            'no_groups' => 'Chưa có nhóm nào.',
            'no_group_items' => 'Chưa có sản phẩm nào.',
        ],
        'price_summary' => [
            'title' => 'Chi tiết giá',
            'base_total' => 'Tổng giá gốc',
            'discount' => 'Giảm giá',
            'final_total' => 'Giá cuối',
            'estimate_note' => 'Ước tính theo lựa chọn tối thiểu của mỗi nhóm.',
            'empty' => 'Thêm sản phẩm để xem giá.',
            'loading' => 'Đang tính...',
        ],
        'tips_list' => [
            'fixed_total' => 'Dùng fixed_total khi muốn combo có tổng giá cố định.',
            'percent_off' => 'Dùng percent_off cho kiểu "Giảm 10% khi mua kèm".',
            'attach' => 'Gắn vào sản phẩm để hiển thị combo trên trang chi tiết sản phẩm (theo include).',
        ],
    ],

    'shortcode' => [
        'name' => 'Combo sản phẩm',
        'description' => 'Hiển thị UI combo theo product_id.',
        'missing_product' => 'Thiếu product_id cho shortcode.',
    ],

    'shortcode_groups' => [
        'name' => 'Nhóm combo',
        'description' => 'Hiển thị danh sách combo (dạng thẻ có ảnh) giống sản phẩm.',
        'title' => 'Tiêu đề',
        'subtitle' => 'Phụ đề',
        'limit' => 'Giới hạn',
        'layout' => 'Kiểu hiển thị',
        'type' => 'Bộ lọc loại',
        'featured_only' => 'Chỉ combo nổi bật',
        'layouts' => [
            'grid' => 'Lưới',
            'tabs' => 'Tab (sắp có)',
            'columns' => 'Cột',
        ],
        'types' => [
            'all' => 'Tất cả',
            'fixed' => 'Combo cố định',
            'mix' => 'Mix & match',
        ],
    ],

    'messages' => [
        'saved' => 'Lưu combo thành công.',
        'deleted' => 'Xóa combo thành công.',
    ],

    'saved' => 'Lưu combo thành công.',
    'deleted' => 'Xóa combo thành công.',

    'front' => [
        'title' => 'Combo',
        'contains_title' => 'Combo bao gồm các sản phẩm sau',
        'view_combo' => 'Xem combo',
        'add_combo' => 'Thêm combo',
        'choose_between' => '(chọn :min-:max)',
        'group_between' => 'Lựa chọn cho nhóm phải từ :min đến :max.',
        'bundle_not_available' => 'Combo này hiện không khả dụng.',
        'group_requires_between' => 'Nhóm ":name" yêu cầu chọn từ :min đến :max sản phẩm.',
        'invalid_selection' => 'Lựa chọn không hợp lệ.',
        'nothing_to_add' => 'Không có gì để thêm.',
        'bundle_added' => 'Đã thêm combo vào giỏ hàng.',
        'request_failed' => 'Yêu cầu thất bại.',
        'failed_to_add' => 'Thêm không thành công.',
        'price_from' => 'Từ',
        'from_price' => 'Từ :price',
        'bundle_price' => 'Giá',
        'featured' => 'Nổi bật',
        'view_details_for_price' => 'Xem chi tiết',
    ],

    'admin_js' => [
        'product_loading' => 'Đang tải...',
    ],
];
